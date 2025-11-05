<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\Register;
use App\Controllers\Subscription;

/**
 * @var RouteCollection $routes
 */

// Root route - home page
$routes->get('/', 'Home::index', ['as' => 'home']);

// History rating routes (non-prefixed for compatibility)
$routes->get('history/rate/(:num)', 'HistoryController::rateHaircut/$1');
$routes->post('history/submit-rating/(:num)', 'HistoryController::submitRating/$1');

// Ensure group root resolves: /CleanCut and /CleanCut/
$routes->get('CleanCut', 'Home::index');
$routes->get('CleanCut/', 'Home::index');

// Temporary: Direct history routes for testing (without CleanCut prefix)
$routes->get('history', 'HistoryController::index');
$routes->get('history/create', 'HistoryController::create');
$routes->post('history/store', 'HistoryController::store');
$routes->get('history/view/(:num)', 'HistoryController::view/$1');
$routes->get('history/edit/(:num)', 'HistoryController::edit/$1');
$routes->post('history/update/(:num)', 'HistoryController::update/$1');
$routes->post('history/delete/(:num)', 'HistoryController::delete/$1');

// Debug route to test if routes are working
$routes->get('test-route', function () {
    return 'Routes are working! Current time: ' . date('Y-m-d H:i:s');
});

// Handle CleanCut/CleanCut redirects to fix double prefix issue
$routes->get('CleanCut/CleanCut/(:any)', function ($path) {
    return redirect()->to('/CleanCut/' . $path, 301);
});

// Compatibility for accidental double-prefixed POST on rating submit
$routes->post('CleanCut/CleanCut/history/submit-rating/(:num)', 'HistoryController::submitRating/$1');

$routes->get('CleanCut/CleanCut', function () {
    return redirect()->to('/CleanCut/', 301);
});

// CleanCut prefixed routes for all existing routes
$routes->group('CleanCut', function ($routes) {
    // Public routes
    $routes->get('login', 'Auth::login', ['as' => 'login']);
    $routes->post('login', 'Auth::authenticate', ['as' => 'authenticate']);
    $routes->get('logout', 'Auth::logout', ['as' => 'logout']);
    $routes->get('register', [Register::class, 'index']);
    $routes->post('register', 'Register::store');


    $routes->get('subscriptions', [Subscription::class, 'subscription'], ['as' => 'subscription']);
    $routes->post('subscriptions/registration', [Subscription::class, 'subscriptionPlan'], ['as' => 'subplan']);
    $routes->post('subscriptions/registration/payment', [Subscription::class, 'subscriptionRegistration'], ['as' => 'subregistration-payment']);
    $routes->get('subscriptions/registration/cancel', [Subscription::class,   'subscriptionCancel'], ['as' => 'subcancel']);
    $routes->get('subscriptions/registration/success', [Subscription::class, 'subscriptionSuccess'], ['as' => 'subsuccess']);

    // Temporary: Make history routes accessible without authentication for testing
    $routes->get('history', 'HistoryController::index');
    $routes->get('history/create', 'HistoryController::create');
    $routes->post('history/store', 'HistoryController::store');
    $routes->get('history/view/(:num)', 'HistoryController::view/$1');
    $routes->get('history/edit/(:num)', 'HistoryController::edit/$1');
    $routes->post('history/update/(:num)', 'HistoryController::update/$1');
    $routes->post('history/delete/(:num)', 'HistoryController::delete/$1');

    // Subscription routes
    $routes->get('subscriptions', 'Subscription::subscription', ['as' => 'subscription']);
    $routes->post('subscriptions/registration', 'Subscription::subscriptionPlan', ['as' => 'subplan']);
    $routes->post('subscriptions/registration/payment', 'Subscription::subscriptionRegistration', ['as' => 'subregistration-payment']);
    $routes->get('subscriptions/registration/cancel', 'Subscription::subscriptionCancel', ['as' => 'subcancel']);
    $routes->get('subscriptions/registration/success', 'Subscription::subscriptionSuccess', ['as' => 'subsuccess']);

    // Social feed (public)
    $routes->get('social-feed', 'SocialFeedController::index');
    $routes->get('social-feed/image/(:num)', 'SocialFeedController::image/$1');
    $routes->get('social-feed/post/(:num)/comments', 'SocialFeedController::comments/$1');
    // Public file serving from writable (limited)
    $routes->get('file/writable', 'FileController::writable');
    $routes->get('social-feed/work-showcase', 'SocialFeedController::workShowcase');
    $routes->get('social-feed/status-updates', 'SocialFeedController::statusUpdates');
    $routes->get('social-feed/trending', 'SocialFeedController::trending');
    $routes->get('social-feed/search', 'SocialFeedController::search');
    $routes->get('social-feed/post/(:num)', 'SocialFeedController::show/$1');
    $routes->get('social-feed/user/(:num)', 'SocialFeedController::userPosts/$1');
    $routes->get('social-feed/barber/(:num)', 'SocialFeedController::barberPortfolio/$1');

    // Service routes (public listing)
    $routes->get('services', 'Services::index');

    // Temporary: Make available-slots accessible without authentication for testing
    $routes->post('booking/available-slots', 'BookingController::getAvailableSlots');

    // Client booking routes
    $routes->get('booking/client', 'BookingController::clientView');
    $routes->get('booking/shops', 'BookingController::getShops');
    $routes->get('booking/my-appointments', 'BookingController::getMyAppointments');

    // Temporary: Make booking accessible without authentication for testing
    $routes->post('booking/book', 'BookingController::book');
        // Payments
        $routes->post('payments/checkout/(:num)', 'PaymentController::createCheckout/$1');
        $routes->post('payments/webhook', 'PaymentController::webhook');
        $routes->get('payments/success', 'PaymentController::success');
        $routes->get('payments/cancel', 'PaymentController::cancel');

    // Real-time validation endpoint (public for API testing)
    $routes->post('booking/validate-slot', 'BookingController::validateTimeSlot');

    // AJAX routes for booking page
    $routes->get('booking/shop-barbers/(:num)', 'BookingController::getShopBarbers/$1');
    $routes->get('booking/shop-services/(:num)', 'BookingController::getServicesForShop/$1');

    // Temporary: Make appointment management accessible without authentication for testing
    $routes->post('appointments/update-status/(:num)', 'AppointmentController::updateStatus/$1');
    $routes->get('appointments/details/(:num)', 'AppointmentController::getDetails/$1');

    // Temporary: Make notification and chat routes accessible without authentication for testing
    $routes->get('notifications/count', 'NotificationController::getCount');
    $routes->get('chat/unread-count', 'ChatController::getUnreadCount');

    // Protected routes
    $routes->group('', ['filter' => 'auth'], function ($routes) {
        // Dashboard routes
        $routes->get('dashboard', 'DashboardController::index');
        $routes->get('admin/dashboard', 'Admin::dashboard');
        $routes->get('customer/dashboard', 'Customer::dashboard');
        $routes->get('barber/dashboard', 'BarberController::dashboard');
        $routes->get('owner/dashboard', 'OwnerController::dashboard');

        // All other protected routes...
        $routes->get('booking', 'BookingController::index');
        $routes->get('profile', 'ProfileController::index');
        $routes->post('profile/update', 'ProfileController::update');
        $routes->post('profile/update-shop', 'ProfileController::updateShop');
        $routes->post('profile/upload-photo', 'ProfileController::uploadPhoto');
        $routes->get('schedule', 'ScheduleController::index');
        $routes->get('appointments', 'AppointmentController::index');
        // History rating routes
        $routes->get('history/rate/(:num)', 'HistoryController::rateHaircut/$1');
        $routes->post('history/submit-rating/(:num)', 'HistoryController::submitRating/$1');
        $routes->get('chat', 'ChatController::index');
        // Chat AJAX endpoints
        $routes->get('chat/conversation/(:num)/messages', 'ChatController::conversation/$1/messages');
        $routes->get('chat/conversation/(:num)', 'ChatController::conversation/$1');
        $routes->post('chat/send', 'ChatController::send');
        $routes->get('chat/send-message', 'ChatController::sendMessageGet');
        $routes->post('chat/upload', 'ChatController::upload');
        $routes->get('chat/recent', 'ChatController::recent');
        $routes->get('chat/online-users', 'ChatController::onlineUsers');
        $routes->post('chat/mark-read', 'ChatController::markAsRead');
        $routes->get('analytics', 'AnalyticsController::index');
        $routes->post('analytics/data', 'AnalyticsController::getData');
        $routes->get('analytics/barbers', 'AnalyticsController::getBarbers');
        $routes->get('analytics/export', 'AnalyticsController::exportAnalytics');
        $routes->get('analytics/export-earnings', 'AnalyticsController::exportEarnings');
        $routes->get('commission', 'CommissionController::index');
        $routes->post('commission/update', 'CommissionController::update');
        $routes->get('users', 'UserController::index');
        $routes->get('services', 'Services::index');
        $routes->get('services/create', 'Services::create');
        $routes->post('services/store', 'Services::store');
        $routes->get('services/edit/(:num)', 'Services::edit/$1');
        $routes->post('services/update/(:num)', 'Services::update/$1');
        $routes->post('services/delete/(:num)', 'Services::delete/$1');
        $routes->get('services/employees', 'Services::employees');
        $routes->post('services/employees/assign', 'Services::assignBarber');
        $routes->get('notifications', 'NotificationController::index');
        $routes->get('notifications/unread', 'NotificationController::getUnread');
        $routes->get('notifications/all', 'NotificationController::getAll');
        $routes->get('notifications/type/(:any)', 'NotificationController::getByType/$1');
        $routes->get('notifications/view/(:num)', 'NotificationController::view/$1');
        $routes->get('notifications/stats', 'NotificationController::getStats');
        $routes->post('notifications/mark-read/(:num)', 'NotificationController::markAsRead/$1');
        $routes->post('notifications/mark-all-read', 'NotificationController::markAllAsRead');
        $routes->post('notifications/send-reminders', 'NotificationController::sendReminders');

        // Social feed protected routes
        $routes->get('social-feed/create', 'SocialFeedController::create');
        $routes->post('social-feed/store', 'SocialFeedController::store');
        $routes->post('social-feed/post/(:num)/like', 'SocialFeedController::like/$1');
        $routes->post('social-feed/post/(:num)/unlike', 'SocialFeedController::unlike/$1');
        $routes->delete('social-feed/post/(:num)', 'SocialFeedController::delete/$1');
        $routes->post('social-feed/post/(:num)/comment', 'SocialFeedController::addComment/$1');

        // Schedule management routes
        $routes->get('schedule', 'ScheduleController::index');
        $routes->get('schedule/weekly', 'ScheduleController::weekly');
        $routes->get('schedule/auto-generator', 'ScheduleController::autoGenerator');
        $routes->post('schedule/update-availability', 'ScheduleController::updateAvailability');
        $routes->post('schedule/available-slots', 'ScheduleController::getAvailableSlots');
        $routes->get('schedule/barber-availability', 'ScheduleController::getBarberAvailability');
        $routes->post('schedule/set-weekly', 'ScheduleController::setWeeklySchedule');
        $routes->post('schedule/copy-week', 'ScheduleController::copyWeekSchedule');
        $routes->post('schedule/generate-auto', 'ScheduleController::generateAutoSchedule');
        $routes->get('schedule/test', 'ScheduleController::testRoute');

        // Legacy barber schedule routes (keep for compatibility)
        $routes->post('barber/update-availability', 'DashboardController::updateAvailability');
        $routes->get('barber/get-availability', 'DashboardController::getAvailability');
        $routes->post('barber/test-update', 'DashboardController::testUpdateAvailability');
    });
});

// Debug routes (public)
$routes->get('debug-role', function () {
    $session = \Config\Services::session();
    $db = \Config\Database::connect();

    echo "<h2>Session and Role Debug</h2>";

    echo "<h3>Session Data:</h3>";
    echo "<p>user_id: " . ($session->get('user_id') ?? 'NOT SET') . "</p>";
    echo "<p>role: " . ($session->get('role') ?? 'NOT SET') . "</p>";
    echo "<p>user_role: " . ($session->get('user_role') ?? 'NOT SET') . "</p>";
    echo "<p>email: " . ($session->get('email') ?? 'NOT SET') . "</p>";

    $userId = $session->get('user_id');
    if ($userId) {
        $user = $db->table('users')->where('user_id', $userId)->get()->getRowArray();
        if ($user) {
            echo "<h3>Database User Data:</h3>";
            echo "<p>User ID: " . $user['user_id'] . "</p>";
            echo "<p>Name: " . $user['first_name'] . " " . $user['last_name'] . "</p>";
            echo "<p>Email: " . $user['email'] . "</p>";
            echo "<p>Role in DB: " . $user['role'] . "</p>";

            // Check if user has a shop
            $shop = $db->table('shops')->where('owner_id', $userId)->get()->getRowArray();
            if ($shop) {
                echo "<p style='color: green;'>✓ User owns shop: " . $shop['shop_name'] . "</p>";
            } else {
                echo "<p style='color: red;'>✗ User has no shop</p>";
            }
        }
    }

    echo "<p><a href='/CleanCut/dashboard'>Go to Dashboard</a></p>";
});

$routes->get('db-test', 'Home::dbTest');

// Cron job routes (automated tasks)
$routes->group('cron', function ($routes) {
    $routes->get('run-all', 'CronController::runAutomatedTasks');
    $routes->get('appointments', 'CronController::runAppointmentAutomation');
    $routes->get('reminders', 'CronController::sendAppointmentReminders');
    $routes->get('cleanup', 'CronController::cleanupOldData');
    $routes->get('earnings', 'CronController::processEarningsSummaries');
    $routes->get('test', 'CronController::test');
    $routes->get('status', 'CronController::status');
});

// Automated appointment management routes
$routes->group('automation', ['filter' => 'auth'], function ($routes) {
    $routes->post('confirm-appointments', 'AutomatedAppointmentController::autoConfirmAppointments');
    $routes->post('complete-appointments', 'AutomatedAppointmentController::autoCompleteAppointments');
    $routes->post('cancel-no-shows', 'AutomatedAppointmentController::autoCancelNoShows');
    $routes->post('send-reminders', 'AutomatedAppointmentController::sendAppointmentReminders');
    $routes->post('run-all', 'AutomatedAppointmentController::runAutomatedTasks');
});

// Chat test routes (for debugging)
$routes->group('chat-test', function ($routes) {
    $routes->get('test', 'ChatTestController::test');
    $routes->post('test-send', 'ChatTestController::testSend');
    $routes->get('test-db', 'ChatTestController::testDb');
    $routes->get('debug', 'ChatTestController::debug');
    $routes->get('get-recent-messages', 'ChatTestController::getRecentMessages');
});
