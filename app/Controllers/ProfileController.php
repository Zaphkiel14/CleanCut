<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ProfileController
 *
 * ProfileController provides a convenient place for loading components
 * and performing functions that are needed by all other controllers.
 * Extend this class in any new controllers:
 *     class Home extends ProfileController
 *
 * For security be sure to declare any new methods as protected or private.
 */
class ProfileController extends BaseController
{
    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend ProfileController.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
    }

    /**
     * Display user profile page
     */
    public function index()
    {
        // Check if user is logged in
        if (!session()->get('user_id')) {
            return redirect()->to('login');
        }

        $user_id = session()->get('user_id');

        // Load user data from database
        $db = \Config\Database::connect();
        $builder = $db->table('users');
        $user = $builder->where('user_id', $user_id)->get()->getRowArray();

        if (!$user) {
            session()->destroy();
            return redirect()->to('login');
        }

        // Load shop information if user is an owner
        $shop = null;
        if ($user['role'] === 'owner') {
            $shopBuilder = $db->table('shops');
            $shop = $shopBuilder->where('owner_id', $user_id)->get()->getRowArray();
        }

        // Determine if barber is freelance (no employees record)
        $isFreelanceBarber = false;
        if ($user['role'] === 'barber') {
            $employee = $db->table('employees')->where('user_id', $user_id)->get()->getRowArray();
            $isFreelanceBarber = empty($employee);
        }

        $data = [
            'title' => 'Profile',
            'user' => $user,
            'shop' => $shop,
            'is_freelance_barber' => $isFreelanceBarber
        ];

        return view('profile/index', $data);
    }

    /**
     * Update user profile
     */
    public function update()
    {
        // Debug logging
        log_message('info', 'Profile update request received');
        log_message('info', 'Request method: ' . $this->request->getMethod());
        log_message('info', 'User ID in session: ' . (session()->get('user_id') ?? 'NOT SET'));
        log_message('info', 'Request headers: ' . json_encode($this->request->getHeaders()));
        log_message('info', 'Request body: ' . json_encode($this->request->getPost()));
        
        // Set AJAX header
        $this->request->setHeader('X-Requested-With', 'XMLHttpRequest');

        // Check if user is logged in
        if (!session()->get('user_id')) {
            log_message('error', 'Profile update failed: User not authenticated');
            return $this->response->setJSON(['success' => false, 'error' => 'Not authenticated']);
        }

        $user_id = session()->get('user_id');

        // Get form data
        $first_name = $this->request->getPost('first_name');
        $last_name = $this->request->getPost('last_name');
        $email = $this->request->getPost('email');
        $phone = $this->request->getPost('phone');

        // Debug logging
        log_message('info', 'Form data received: first_name=' . $first_name . ', last_name=' . $last_name . ', email=' . $email . ', phone=' . $phone);

        // Validate required fields
        if (empty($first_name) || empty($last_name) || empty($email)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Please fill in all required fields']);
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Please enter a valid email address']);
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('users');

            // Check if email is already taken by another user
            $existing_user = $builder->where('email', $email)
                ->where('user_id !=', $user_id)
                ->get()
                ->getRowArray();

            if ($existing_user) {
                return $this->response->setJSON(['success' => false, 'error' => 'Email address is already taken']);
            }

            // Update user data
            $update_data = [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone
            ];

            // Optional: freelance barber booking fee
            if ((session()->get('role') === 'barber')) {
                $fee = $this->request->getPost('freelance_booking_fee_percentage');
                if ($fee !== null && $fee !== '') {
                    $feeNum = (float) $fee;
                    if ($feeNum < 0 || $feeNum > 100) {
                        return $this->response->setJSON(['success' => false, 'error' => 'Booking fee must be between 0 and 100%']);
                    }
                    $update_data['freelance_booking_fee_percentage'] = number_format($feeNum, 2, '.', '');
                }
            }

            $builder->where('user_id', $user_id)->update($update_data);

            // Update session data
            session()->set([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'user_name' => $first_name . ' ' . $last_name
            ]);

            return $this->response->setJSON(['success' => true, 'message' => 'Profile updated successfully']);
        } catch (\Exception $e) {
            log_message('error', 'Profile update error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'error' => 'Failed to update profile. Please try again.']);
        }
    }

    /**
     * Upload profile photo
     */
    public function uploadPhoto()
    {
        // Check if user is logged in
        if (!session()->get('user_id')) {
            return $this->response->setJSON(['success' => false, 'error' => 'Not authenticated']);
        }

        $user_id = session()->get('user_id');

        // Check if file was uploaded
        $file = $this->request->getFile('profile_photo');

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Please select a valid image file']);
        }

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($file->getMimeType(), $allowed_types)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Please upload a valid image file (JPG, PNG, GIF)']);
        }

        // Validate file size (max 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            return $this->response->setJSON(['success' => false, 'error' => 'File size must be less than 5MB']);
        }

        try {
            // Generate unique filename
            $filename = 'profile_' . $user_id . '_' . time() . '.' . $file->getExtension();

            // Move file to uploads directory
            $upload_path = FCPATH . 'uploads/profiles/';
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            // Ensure directory is writable
            if (!is_writable($upload_path)) {
                chmod($upload_path, 0777);
            }

            if (!$file->move($upload_path, $filename)) {
                return $this->response->setJSON(['success' => false, 'error' => 'Failed to move uploaded file']);
            }

            // Update database with new photo path
            $db = \Config\Database::connect();
            $builder = $db->table('users');

            $photo_path = 'uploads/profiles/' . $filename;
            $builder->where('user_id', $user_id)->update([
                'profile_photo' => $photo_path
            ]);

            // Update session
            session()->set('profile_photo', $photo_path);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Profile photo updated successfully',
                'photo_path' => base_url($photo_path)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Profile photo upload error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'error' => 'Failed to upload photo. Please try again.']);
        }
    }

    /**
     * Update shop information
     */
    public function updateShop()
    {
        // Check if user is logged in and is an owner
        if (!session()->get('user_id') || session()->get('role') !== 'owner') {
            return $this->response->setJSON(['success' => false, 'error' => 'Not authorized']);
        }

        $user_id = session()->get('user_id');

        // Get form data
        $shop_name = $this->request->getPost('shop_name');
        $shop_address = $this->request->getPost('shop_address');
        $booking_fee_percentage = $this->request->getPost('booking_fee_percentage');

        // Validate required fields
        if (empty($shop_name)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Shop name is required']);
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('shops');

            // Check if shop exists for this owner
            $shop = $builder->where('owner_id', $user_id)->get()->getRowArray();

            if ($shop) {
                // Update existing shop
                $update_data = [
                    'shop_name' => $shop_name,
                    'address' => $shop_address
                ];
                if ($booking_fee_percentage !== null && $booking_fee_percentage !== '') {
                    $feeNum = (float) $booking_fee_percentage;
                    if ($feeNum < 0 || $feeNum > 100) {
                        return $this->response->setJSON(['success' => false, 'error' => 'Booking fee must be between 0 and 100%']);
                    }
                    $update_data['booking_fee_percentage'] = number_format($feeNum, 2, '.', '');
                }

                $builder->where('owner_id', $user_id)->update($update_data);
            } else {
                // Create new shop
                $insert_data = [
                    'owner_id' => $user_id,
                    'shop_name' => $shop_name,
                    'address' => $shop_address,
                    'is_active' => 1
                ];
                if ($booking_fee_percentage !== null && $booking_fee_percentage !== '') {
                    $feeNum = (float) $booking_fee_percentage;
                    if ($feeNum < 0 || $feeNum > 100) {
                        return $this->response->setJSON(['success' => false, 'error' => 'Booking fee must be between 0 and 100%']);
                    }
                    $insert_data['booking_fee_percentage'] = number_format($feeNum, 2, '.', '');
                }

                $builder->insert($insert_data);
            }

            // Update session with new shop name
            session()->set('shop_name', $shop_name);

            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Shop information updated successfully',
                'shop_name' => $shop_name
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Shop update error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'error' => 'Failed to update shop information. Please try again.']);
        }
    }
}
