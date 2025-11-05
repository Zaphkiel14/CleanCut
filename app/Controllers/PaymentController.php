<?php

namespace App\Controllers;

class PaymentController extends BaseController
{
    /**
     * Create a PayMongo Checkout session for the booking fee
     */
    public function createCheckout($appointmentId)
    {
        $db = \Config\Database::connect();
        $appointment = $db->table('appointments')->where('appointment_id', (int)$appointmentId)->get()->getRowArray();
        if (!$appointment) {
            return $this->response->setJSON(['error' => 'Appointment not found'])->setStatusCode(404);
        }

        if ((float)$appointment['booking_fee_amount'] <= 0) {
            return $this->response->setJSON(['error' => 'No booking fee due'])->setStatusCode(400);
        }

        $amountCentavos = (int) round(((float)$appointment['booking_fee_amount']) * 100);

        // Delegate to shared PaymongoService to avoid affecting subscriptions
        $service = new \App\Services\PaymongoService();
        $response = $service->createCheckoutForBooking(
            $amountCentavos,
            'Booking Fee',
            'Booking fee for appointment #' . $appointmentId,
            base_url('payments/success?appointment_id=' . $appointmentId),
            base_url('payments/cancel?appointment_id=' . $appointmentId),
            [
                'appointment_id' => (int)$appointmentId,
                'customer_id' => (int)$appointment['customer_id'],
                'barber_id' => (int)$appointment['barber_id'],
            ]
        );

        if (!is_array($response) || empty($response['data']['attributes']['checkout_url'])) {
            return $this->response->setJSON(['error' => 'Payment error', 'provider_response' => $response])->setStatusCode(500);
        }

        $db->table('appointments')->where('appointment_id', (int)$appointmentId)->update([
            'payment_status' => 'pending',
            'payment_provider' => 'paymongo',
            'payment_reference' => $response['data']['id'] ?? null,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'checkout_url' => $response['data']['attributes']['checkout_url']
        ]);
    }

    /**
     * Webhook to update appointment on payment events
     */
    public function webhook()
    {
        $payload = $this->request->getBody();
        $data = json_decode($payload, true);
        if (!$data) {
            return $this->response->setStatusCode(400)->setBody('Invalid');
        }

        // Basic parse for appointment_id from metadata
        $attributes = $data['data']['attributes'] ?? [];
        $metadata = $attributes['metadata'] ?? [];
        $appointmentId = $metadata['appointment_id'] ?? null;
        $eventType = $data['data']['type'] ?? ($attributes['status'] ?? '');

        if ($appointmentId) {
            $db = \Config\Database::connect();
            if (strpos($eventType, 'paid') !== false || strpos($eventType, 'succeeded') !== false) {
                $db->table('appointments')->where('appointment_id', (int)$appointmentId)->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed'
                ]);
            } elseif (strpos($eventType, 'failed') !== false || strpos($eventType, 'canceled') !== false) {
                $db->table('appointments')->where('appointment_id', (int)$appointmentId)->update([
                    'payment_status' => 'failed'
                ]);
            }
        }

        return $this->response->setStatusCode(200)->setBody('ok');
    }

    /**
     * Success return after payment - show alert and redirect
     */
    public function success()
    {
        $appointmentId = $this->request->getGet('appointment_id');
        $redirectUrl = base_url('appointments');
        $message = 'Payment completed. Your booking is confirmed!';

        // Fallback: in case webhook is not configured, mark as paid/confirmed here
        if ($appointmentId) {
            try {
                $db = \Config\Database::connect();
                $db->table('appointments')->where('appointment_id', (int)$appointmentId)->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed'
                ]);
            } catch (\Throwable $e) {
                // ignore and still redirect; status will be set by webhook
            }
        }

        // Render minimal page to alert then redirect
        return $this->response->setBody(
            '<script>alert(' . json_encode($message) . '); window.location.href=' . json_encode($redirectUrl) . ';</script>'
        );
    }

    /**
     * Cancel return after payment - notify and redirect
     */
    public function cancel()
    {
        $appointmentId = $this->request->getGet('appointment_id');
        $redirectUrl = base_url('booking');
        $message = 'Payment was cancelled. Your booking remains pending.';

        return $this->response->setBody(
            '<script>alert(' . json_encode($message) . '); window.location.href=' . json_encode($redirectUrl) . ';</script>'
        );
    }
}
