<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'CleanCut - Haircut Management System' ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Performance: remove Chart.js from global layout; include only on pages that need it -->
    
    <!-- Hint browsers to establish early connections for CDN assets -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
        }
        
        body {
            background-color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .sidebar {
            min-height: calc(100vh - 56px);
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.25rem;
            margin: 0.25rem 0;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.2);
            color: white;
            font-weight: bold;
        }
        
        .main-content {
            padding: 2rem 0;
            background-color: #ffffff;
            min-height: calc(100vh - 56px);
        }
        
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0, 123, 255, 0.1);
            border-radius: 0.75rem;
            background-color: #ffffff;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            color: white;
            border-radius: 0.5rem 0.5rem 0 0 !important;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            border: none;
            border-radius: 0.375rem;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0,123,255,0.3);
        }
        
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: bold;
        }
        
        .status-pending { background-color: var(--warning-color); color: #212529; }
        .status-confirmed { background-color: var(--info-color); color: white; }
        .status-completed { background-color: var(--success-color); color: white; }
        .status-cancelled { background-color: var(--danger-color); color: white; }
        
        .dashboard-card {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 6px rgba(0, 123, 255, 0.1);
        }
        
        .dashboard-card .icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        
        .dashboard-card .number {
            font-size: 2rem;
            font-weight: bold;
        }
        
        .dashboard-card .label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 123, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 56px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 56px);
                z-index: 1000;
                transition: left 0.3s ease;
            }
            
            .sidebar.show {
                left: 0;
            }
        }
    </style>
</head>
<body data-user-id="<?= session()->get('user_id') ?? '' ?>" data-user-role="<?= session()->get('role') ?? session()->get('user_role') ?? '' ?>">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('/') ?>">
                <i class="fas fa-cut text-primary"></i> CleanCut
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (session()->get('user_id')): ?>
                        <?php $navRole = session()->get('role') ?? session()->get('user_role'); ?>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (session()->get('user_id')): ?>
                        <!-- Notifications -->
                        <li class="nav-item dropdown me-2">
                            <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" id="notificationDropdown">
                                <i class="fas fa-bell"></i>
                                <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle notification-count-badge" style="display: none;">0</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end notification-dropdown" style="width: 350px; max-height: 400px; overflow-y: auto;">
                                <div class="dropdown-header d-flex justify-content-between align-items-center">
                                    <span><strong>Notifications</strong></span>
                                    <a href="<?= base_url('notifications') ?>" class="text-primary small">View All</a>
                                </div>
                                <div id="notificationsList">
                                    <div class="text-center p-3">
                                        <i class="fas fa-spinner fa-spin"></i> Loading...
                                    </div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <div class="dropdown-footer text-center">
                                    <button class="btn btn-sm btn-outline-primary" onclick="markAllNotificationsRead()">
                                        Mark All Read
                                    </button>
                                </div>
                            </div>
                        </li>

                        <!-- Social Feed (Customer Only) -->
                        <?php if ($navRole === 'customer'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('social-feed') ?>">
                                    <i class="fas fa-users"></i> Social Feed
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- User Menu -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" 
                               data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> 
                                <?php 
                                $currentRole = session()->get('role') ?? session()->get('user_role');
                                if ($currentRole === 'owner' && session()->get('shop_name')) {
                                    echo session()->get('shop_name');
                                } else {
                                    echo session()->get('user_name') ?? 'User';
                                }
                                ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= base_url('profile') ?>">
                                    <i class="fas fa-user"></i> Profile
                                </a></li>
                                <li><a class="dropdown-item" href="<?= base_url('notifications') ?>">
                                    <i class="fas fa-bell"></i> Notifications
                                </a></li>
                                <li><a class="dropdown-item" href="<?= base_url('chat') ?>">
                                    <i class="fas fa-comments"></i> Messages
                                    <span id="message-count" class="badge bg-danger ms-2" style="display: none;">0</span>
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= base_url('logout') ?>">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('subscriptions') ?>">
                                <i class="fas fa-crown"></i> Subscriptions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('login') ?>">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('register') ?>">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <?php if (session()->get('user_id')): ?>
                <!-- Sidebar -->
                <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse" id="sidebar">
                    <div class="position-sticky pt-3">
                        <?php $currentRole = session()->get('role') ?? session()->get('user_role'); ?>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('dashboard') ?>">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            </li>
                            
                            <?php if (in_array($currentRole, ['customer'])): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('booking') ?>">
                                        <i class="fas fa-calendar-plus"></i> Book Appointment
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('history') ?>">
                                        <i class="fas fa-history"></i> Haircut History
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php if (in_array($currentRole, ['barber'])): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('analytics') ?>">
                                        <i class="fas fa-chart-line"></i> Earnings
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('history') ?>">
                                        <i class="fas fa-history"></i> Haircut History
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('social-feed') ?>">
                                        <i class="fas fa-camera"></i> Work Showcase
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php if (in_array($currentRole, ['admin'])): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('users') ?>">
                                        <i class="fas fa-users"></i> User Management
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php if (in_array($currentRole, ['admin', 'owner'])): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('services') ?>">
                                        <i class="fas fa-list"></i> Service Management
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('commission') ?>">
                                        <i class="fas fa-percentage"></i> Commission Settings
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('analytics') ?>">
                                        <i class="fas fa-chart-bar"></i> Analytics
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if ($currentRole === 'barber' || $currentRole === 'owner' || $currentRole === 'admin'): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('schedule') ?>">
                                        <i class="fas fa-calendar-alt"></i> Schedule Management
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('schedule/weekly') ?>">
                                        <i class="fas fa-calendar-week"></i> Weekly View
                                    </a>
                                </li>
                            <?php endif; ?>

                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('appointments') ?>">
                                    <i class="fas fa-calendar-check"></i> 
                                    <?php if ($currentRole === 'customer'): ?>
                                        My Appointments
                                    <?php elseif ($currentRole === 'barber'): ?>
                                        My Appointments
                                    <?php else: ?>
                                        Manage Appointments
                                    <?php endif; ?>
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('notifications') ?>">
                                    <i class="fas fa-bell"></i> Notifications
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('chat') ?>">
                                    <i class="fas fa-comments"></i> Messages
                                </a>
                            </li>
                            <?php if ($currentRole === 'customer'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('booking/client') ?>">
                                    <i class="fas fa-calendar-plus"></i> Book Appointment
                                </a>
                            </li>
                            <?php endif; ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= base_url('profile') ?>">
                                    <i class="fas fa-user"></i> Profile
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
            <?php endif; ?>

            <!-- Main content -->
            <main class="<?= session()->get('user_id') ? 'col-md-9 ms-sm-auto col-lg-10' : 'col-12' ?> px-md-4">
                <div class="main-content">
                    <!-- Flash Messages -->
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Page Content -->
                    <?= $this->renderSection('content') ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- WebSocket Client for Real-time Communication -->
    <script src="<?= base_url('assets/js/websocket-client.js') ?>"></script>
    
    <?php if (session()->get('user_id')): ?>
    <script>
    // CSRF helpers for fetch requests
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return '';
    }
    const CSRF_HEADER = '<?= esc((new \Config\Security())->headerName) ?>';
    const CSRF_COOKIE = '<?= esc((new \Config\Security())->cookieName) ?>';
        // Update message count (only when logged in)
        function updateMessageCount() {
            $.get('<?= base_url('chat/unread-count') ?>', function(data) {
                if (data.success && data.unread_count > 0) {
                    $('#message-count').text(data.unread_count).show();
                } else {
                    $('#message-count').hide();
                }
            });
        }

        // Update message count every 30 seconds
        setInterval(updateMessageCount, 30000);
        updateMessageCount();
    </script>
    <?php endif; ?>

    <!-- Notification System JavaScript -->
    <?php if (session()->get('user_id')): ?>
    <script>
    // Load notifications when dropdown is clicked (only when logged in)
    const notifEl = document.getElementById('notificationDropdown');
    if (notifEl) {
        notifEl.addEventListener('click', function(e) {
            e.preventDefault();
            loadNotifications();
        });
    }

    function loadNotifications() {
        fetch('<?= base_url('notifications/unread') ?>', { credentials: 'same-origin' })
        .then(async response => {
            let data;
            try { data = await response.json(); } catch (e) { throw new Error('Invalid response'); }
            return data;
        })
        .then(data => {
            const container = document.getElementById('notificationsList');
            if (!container) return;
            if (data && data.success) {
                displayNotifications(data.notifications || []);
                updateNotificationBadge(data.unread_count || 0);
            } else {
                container.innerHTML = '<div class="text-center p-3 text-muted">No new notifications</div>';
                updateNotificationBadge(0);
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            const container = document.getElementById('notificationsList');
            if (container) {
                container.innerHTML = '<div class="text-center p-3 text-danger">Error loading notifications</div>';
            }
        });
    }

    function displayNotifications(notifications) {
        const container = document.getElementById('notificationsList');
        if (!container) return;
        
        if (notifications.length === 0) {
            container.innerHTML = '<div class="text-center p-3 text-muted">No new notifications</div>';
            return;
        }

        let html = '';
        notifications.forEach(notification => {
            const timeAgo = getTimeAgo(notification.created_at);
            const iconClass = getNotificationIcon(notification.type);
            
            html += `
                <div class="dropdown-item notification-item" data-id="${notification.notification_id}">
                    <div class="d-flex">
                        <div class="me-2">
                            <i class="${iconClass}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold small">${notification.title}</div>
                            <div class="small text-muted">${notification.message.length > 60 ? notification.message.substring(0, 60) + '...' : notification.message}</div>
                            <div class="text-muted small">${timeAgo}</div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }

    function getNotificationIcon(type) {
        const icons = {
            'appointment_confirmed': 'fas fa-check-circle text-success',
            'appointment_cancelled': 'fas fa-times-circle text-danger',
            'appointment_completed': 'fas fa-thumbs-up text-success',
            'appointment_reminder': 'fas fa-clock text-warning',
            'schedule_updated': 'fas fa-calendar-alt text-info',
            'service_added': 'fas fa-plus-circle text-primary',
            'new_appointment': 'fas fa-calendar-check text-info'
        };
        return icons[type] || 'fas fa-bell text-secondary';
    }

    function getTimeAgo(datetime) {
        const now = new Date();
        const time = new Date(datetime);
        const diff = Math.floor((now - time) / 1000);
        
        if (diff < 60) return 'Just now';
        if (diff < 3600) return Math.floor(diff / 60) + ' min ago';
        if (diff < 86400) return Math.floor(diff / 3600) + ' hr ago';
        return Math.floor(diff / 86400) + ' days ago';
    }

    function updateNotificationBadge(count) {
        const badge = document.querySelector('.notification-count-badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'inline';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    function markAllNotificationsRead() {
        fetch('<?= base_url('notifications/mark-all-read') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                [CSRF_HEADER]: getCookie(CSRF_COOKIE)
            },
            credentials: 'same-origin'
        })
        .then(async response => { try { return await response.json(); } catch(e) { throw new Error('Invalid response'); } })
        .then(data => {
            if (data && data.success) {
                updateNotificationBadge(0);
                loadNotifications();
            } else {
                alert('Error: ' + (data && data.error ? data.error : 'Request failed'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: request failed');
        });
    }

    // Load notification count on page load
    document.addEventListener('DOMContentLoaded', function() {
        fetch('<?= base_url('notifications/count') ?>', { credentials: 'same-origin' })
        .then(response => response.json())
        .then(data => {
            updateNotificationBadge(data.count);
        })
        .catch(error => console.error('Error loading notification count:', error));
    });

    // Auto-refresh notifications every 30 seconds
    setInterval(() => {
        fetch('<?= base_url('notifications/count') ?>', { credentials: 'same-origin' })
        .then(response => response.json())
        .then(data => {
            updateNotificationBadge(data.count);
        })
        .catch(error => console.error('Error:', error));
    }, 30000);
    </script>
    <?php endif; ?>

    <style>
    .notification-dropdown {
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .notification-item {
        border-bottom: 1px solid #eee;
        padding: 0.75rem 1rem;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .notification-item:hover {
        background-color: #f8f9fa;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-count-badge {
        font-size: 0.75rem;
        min-width: 1.25rem;
        height: 1.25rem;
        line-height: 1.25rem;
    }
    </style>

    <?= $this->renderSection('scripts') ?>
</body>
</html> 