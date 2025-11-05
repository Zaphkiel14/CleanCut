<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Services\PaymongoService;

class Subscription extends BaseController
{
    protected $userModel;
    protected $paymongo;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->paymongo = new PaymongoService();
    }

    public function subscription()
    {
        // If user is already logged in, redirect to dashboard
        if (session()->get('user_id')) {
            return redirect()->to('dashboard');
        }

        return view('subscription/subscription', ['title' => 'Subscriptions - CleanCut']);
    }

    public function subscriptionPlan()
    {
        // If user is already logged in, redirect to dashboard
        if (session()->get('user_id')) {
            return redirect()->to('dashboard');
        }

        $planData = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'amount' => $this->request->getPost('price'),
            'tier' => $this->request->getPost('tier'),
            'role' => $this->request->getPost('role')
        ];

        // Store temporarily in session
        session()->set('selected_plan', $planData);

        // Log session values for debugging
        log_message('debug', '[SubscriptionPlan] Session selected_plan: ' . json_encode(session()->get('selected_plan')));

        return view('subscription/subregistration', ['title' => 'Subscription Registration- CleanCut']);
    }

    public function subscriptionRegistration()
    {
        // If user is already logged in, redirect to dashboard
        if (session()->get('user_id')) {
            return redirect()->to('dashboard');
        }

        // Validate input
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[100]',
            'last_name' => 'required|min_length[2]|max_length[100]',
            'email'      => 'required|valid_email|is_unique[users.email]',
            'password'   => 'required|min_length[6]',
            'confirm_password' => 'required|matches[password]',
            'role' => 'required|in_list[customer,barber,owner,admin]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Store user data (do not create user YET)
        $pendingUserData = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name'  => $this->request->getPost('last_name'),
            'email'      => $this->request->getPost('email'),
            'password'   => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role' => $this->request->getPost('role'),
            'is_active'  => true
        ];
        // Streamline marking as freelancer
        $role = $this->request->getPost('role');
        if ($role === 'barber' && ($this->request->getPost('is_freelancer') || session('selected_plan.tier') === 'freelancer')) {
            $pendingUserData['is_freelancer'] = 1;
        }

        if ($this->request->getPost('contact_info')) {
            $pendingUserData['phone'] = $this->request->getPost('contact_info');
        }

        // Save the pending user data in the session (temporarily)
        session()->set('pending_user_data', $pendingUserData);

        try {
            // Use PaymongoService to create checkout session and redirect to payment
            $paymongo = new PaymongoService();
            $response = $paymongo->createCheckout();

            if (isset($response['data']['attributes']['checkout_url'])) {
                return redirect()->to($response['data']['attributes']['checkout_url']);
            } else {
                return $this->response->setJSON($response);
            }
        } catch (\Exception $e) {
            log_message('error', 'Checkout initiation failed: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to initiate payment: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * This function should be called by your Paymongo success webhook/callback or after successful payment redirect.
     * It finalizes the registration by creating the user after payment.
     */
    public function subscriptionSuccess()
    {
        // Retrieve user data from session
        $userData = session()->get('pending_user_data');

        if (empty($userData)) {
            session()->setFlashdata('error', 'Unable to complete registration. Your session may have expired.');
            return redirect()->to('subscriptions');
        }

        // Double check again to prevent duplicate emails just in case
        $existingUser = $this->userModel->where('email', $userData['email'])->first();
        if ($existingUser) {
            session()->setFlashdata('error', 'Email is already registered. Please login or use another email.');
            session()->remove('pending_user_data');
            return redirect()->to('subscriptions');
        }

        try {
            $this->userModel->save($userData);
            session()->remove('pending_user_data');
            session()->setFlashdata('success', 'Payment successful! Your subscription has been activated. You may now log in.');
            return redirect()->to('login');
        } catch (\Exception $e) {
            log_message('error', 'User creation after payment failed: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to create account after payment: ' . $e->getMessage());
            return redirect()->to('subscriptions');
        }
    }

    public function subscriptionCancel()
    {
        // Optionally clear pending user session
        session()->remove('pending_user_data');
        // Handle cancelled payment
        session()->setFlashdata('error', 'Payment was cancelled. You can try again anytime.');
        return redirect()->to('subscriptions');
    }

}