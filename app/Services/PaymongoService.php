<?php

namespace App\Services;

use Config\Services;

class PaymongoService
{
    private $apiKey = 'sk_test_nbPmzh41PjTEC6MEAkVfXhoK'; // fallback test key; prefer env

    public function createCheckout()
    {
        $client = Services::curlrequest();

        // Fetch selected plan from session for dynamic values
        $plan = session()->get('selected_plan');

        if (
            !is_array($plan)
            || !isset($plan['amount'])
            || !isset($plan['description'])
            || !isset($plan['name'])
        ) {
            throw new \Exception('Selected plan data is missing in session.');
        }

        $data = [
            'data' => [
                'attributes' => [
                    'send_email_receipt' => false,
                    'show_description' => true,
                    'show_line_items' => true,
                    'line_items' => [
                        [
                            'currency' => 'PHP',
                            'amount' => (int) $plan['amount'],
                            'description' => $plan['description'],
                            'name' => $plan['name'],
                            'quantity' => 1
                        ]
                    ],
                    'payment_method_types' => ['gcash', 'paymaya'],
                    'statement_descriptor' => 'CleanCut',
                    'description' => 'CleanCut Subscription',
                    'success_url' => base_url('subscriptions/registration/success'),
                    'cancel_url' => base_url('subscriptions/registration/cancel'),
                ]
            ]
        ];

        // Log the request data for debugging
        log_message('debug', '[PaymongoService] Checkout request data: ' . json_encode($data));

        try {
            $response = $client->post('https://api.paymongo.com/v1/checkout_sessions', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode((getenv('paymongo.secret_key') ?: env('paymongo.secret_key') ?: $this->apiKey) ),
                ],
                'json' => $data
            ]);
            $result = $response->getBody();
        } catch (\Exception $e) {
            // Log the error like the example shows error output
            log_message('error', '[PaymongoService] Checkout response error: ' . $e->getMessage());
            throw $e;
        }

        // Log the raw result like your cURL PHP sample
        log_message('debug', '[PaymongoService] Checkout response result: ' . $result);

        return json_decode($result, true);
    }

    /**
     * Create a generic checkout for booking fees with metadata
     */
    public function createCheckoutForBooking(int $amountCentavos, string $name, string $description, string $successUrl, string $cancelUrl, array $metadata = [])
    {
        $client = Services::curlrequest();

        $data = [
            'data' => [
                'attributes' => [
                    'send_email_receipt' => false,
                    'show_description' => true,
                    'show_line_items' => true,
                    'line_items' => [[
                        'currency' => 'PHP',
                        'amount' => $amountCentavos,
                        'description' => $description,
                        'name' => $name,
                        'quantity' => 1
                    ]],
                    'payment_method_types' => ['gcash', 'paymaya'],
                    'statement_descriptor' => 'CleanCut',
                    'description' => $description,
                    'success_url' => $successUrl,
                    'cancel_url' => $cancelUrl,
                    'metadata' => $metadata,
                ]
            ]
        ];

        log_message('debug', '[PaymongoService] Booking checkout request: ' . json_encode($data));

        $response = $client->post('https://api.paymongo.com/v1/checkout_sessions', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode((getenv('paymongo.secret_key') ?: env('paymongo.secret_key') ?: $this->apiKey)),
            ],
            'json' => $data
        ]);

        $result = $response->getBody();
        log_message('debug', '[PaymongoService] Booking checkout response: ' . $result);
        return json_decode($result, true);
    }
}
