<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mt-4">
                <i class="fas fa-bell text-primary"></i> Notifications
            </h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Notifications</li>
            </ol>
        </div>
        <div>
            <button class="btn btn-primary" onclick="markAllAsRead()">
                <i class="fas fa-check-double"></i> Mark All as Read
            </button>
            <button class="btn btn-outline-secondary" onclick="refreshNotifications()">
                <i class="fas fa-sync"></i> Refresh
                </button>
        </div>
    </div>

    <!-- Notification Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="total-notifications">0</h4>
                            <p class="mb-0">Total Notifications</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-bell fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="unread-notifications">0</h4>
                            <p class="mb-0">Unread</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="read-notifications">0</h4>
                            <p class="mb-0">Read</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="today-notifications">0</h4>
                            <p class="mb-0">Today</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-day fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="card mb-4">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="notificationTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                        <i class="fas fa-list"></i> All Notifications
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="unread-tab" data-bs-toggle="tab" data-bs-target="#unread" type="button" role="tab">
                        <i class="fas fa-exclamation-circle"></i> Unread
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings" type="button" role="tab">
                        <i class="fas fa-calendar-plus"></i> Bookings
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reminders-tab" data-bs-toggle="tab" data-bs-target="#reminders" type="button" role="tab">
                        <i class="fas fa-clock"></i> Reminders
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="notificationTabContent">
                <!-- All Notifications -->
                <div class="tab-pane fade show active" id="all" role="tabpanel">
                    <div id="all-notifications">
                        <!-- Notifications will be loaded here -->
                    </div>
                </div>
                
                <!-- Unread Notifications -->
                <div class="tab-pane fade" id="unread" role="tabpanel">
                    <div id="unread-notifications-list">
                        <!-- Unread notifications will be loaded here -->
                    </div>
                </div>
                
                <!-- Booking Notifications -->
                <div class="tab-pane fade" id="bookings" role="tabpanel">
                    <div id="booking-notifications">
                        <!-- Booking notifications will be loaded here -->
                    </div>
                                </div>
                
                <!-- Reminder Notifications -->
                <div class="tab-pane fade" id="reminders" role="tabpanel">
                    <div id="reminder-notifications">
                        <!-- Reminder notifications will be loaded here -->
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

<!-- Notification Detail Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationModalTitle">Notification Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="notificationModalBody">
                <!-- Notification details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="markAsReadBtn">Mark as Read</button>
                </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let currentNotificationId = null;

// Load notifications on page load
document.addEventListener('DOMContentLoaded', function() {
    loadNotificationStats();
    loadAllNotifications();
    
    // Set up real-time updates
    setInterval(loadNotificationStats, 30000); // Update every 30 seconds
});

// Load notification statistics
function loadNotificationStats() {
    fetch('/CleanCut/notifications/stats')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('total-notifications').textContent = data.stats.total;
            document.getElementById('unread-notifications').textContent = data.stats.unread;
            document.getElementById('read-notifications').textContent = data.stats.read;
            document.getElementById('today-notifications').textContent = data.stats.today;
        }
    })
    .catch(error => console.error('Error loading stats:', error));
}

// Load all notifications
function loadAllNotifications() {
    fetch('/CleanCut/notifications/all')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayNotifications(data.notifications, 'all-notifications');
        }
    })
    .catch(error => console.error('Error loading notifications:', error));
}

// Load unread notifications
function loadUnreadNotifications() {
    fetch('/CleanCut/notifications/unread')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayNotifications(data.notifications, 'unread-notifications-list');
        }
    })
    .catch(error => console.error('Error loading unread notifications:', error));
}

// Load booking notifications
function loadBookingNotifications() {
    fetch('/CleanCut/notifications/type/booking')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayNotifications(data.notifications, 'booking-notifications');
        }
    })
    .catch(error => console.error('Error loading booking notifications:', error));
}

// Load reminder notifications
function loadReminderNotifications() {
    fetch('/CleanCut/notifications/type/reminder')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayNotifications(data.notifications, 'reminder-notifications');
        }
    })
    .catch(error => console.error('Error loading reminder notifications:', error));
}

// Display notifications
function displayNotifications(notifications, containerId) {
    const container = document.getElementById(containerId);
    
    if (notifications.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">No notifications</h6>
                <p class="text-muted">You're all caught up!</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    notifications.forEach(notification => {
        const isRead = notification.is_read == 1;
        const timeAgo = getTimeAgo(notification.created_at);
        const iconClass = getNotificationIcon(notification.type);
        const badgeClass = isRead ? 'bg-secondary' : 'bg-primary';
        
        html += `
            <div class="notification-item border-bottom py-3 ${isRead ? 'opacity-75' : ''}" 
                 onclick="viewNotification(${notification.notification_id})" 
                 style="cursor: pointer;">
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        <i class="${iconClass} fa-2x text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6 class="mb-1 ${isRead ? '' : 'fw-bold'}">${notification.title}</h6>
                            <div class="d-flex align-items-center">
                                <span class="badge ${badgeClass} me-2">${notification.type}</span>
                                <small class="text-muted">${timeAgo}</small>
                            </div>
                        </div>
                        <p class="mb-1 text-muted">${notification.message}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">${formatDate(notification.created_at)}</small>
                            ${!isRead ? '<span class="badge bg-warning">New</span>' : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Get notification icon based on type
function getNotificationIcon(type) {
    switch(type) {
        case 'booking':
            return 'fas fa-calendar-plus';
        case 'cancellation':
            return 'fas fa-calendar-times';
        case 'reminder':
            return 'fas fa-clock';
        case 'reschedule':
            return 'fas fa-calendar-check';
        default:
            return 'fas fa-bell';
    }
}

// View notification details
function viewNotification(notificationId) {
    currentNotificationId = notificationId;
    
    fetch(`/CleanCut/notifications/view/${notificationId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('notificationModalTitle').textContent = data.notification.title;
            document.getElementById('notificationModalBody').innerHTML = `
                <p><strong>Message:</strong> ${data.notification.message}</p>
                <p><strong>Type:</strong> <span class="badge bg-primary">${data.notification.type}</span></p>
                <p><strong>Date:</strong> ${formatDate(data.notification.created_at)}</p>
                <p><strong>Status:</strong> ${data.notification.is_read == 1 ? 'Read' : 'Unread'}</p>
            `;
            
            // Show mark as read button only if unread
            const markAsReadBtn = document.getElementById('markAsReadBtn');
            if (data.notification.is_read == 0) {
                markAsReadBtn.style.display = 'inline-block';
        } else {
                markAsReadBtn.style.display = 'none';
            }
            
            $('#notificationModal').modal('show');
        }
    })
    .catch(error => console.error('Error loading notification:', error));
}

// Mark notification as read
function markNotificationAsRead(notificationId) {
    fetch(`/CleanCut/notifications/mark-read/${notificationId}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh notifications
            loadAllNotifications();
            loadNotificationStats();
            
            // Close modal if open
            $('#notificationModal').modal('hide');
        }
    })
    .catch(error => console.error('Error marking notification as read:', error));
}

// Mark all notifications as read
function markAllAsRead() {
    if (confirm('Mark all notifications as read?')) {
        fetch('/CleanCut/notifications/mark-all-read', {
            method: 'POST'
        })
        .then(response => response.json())
    .then(data => {
            if (data.success) {
                loadAllNotifications();
                loadNotificationStats();
            }
        })
        .catch(error => console.error('Error marking all as read:', error));
    }
}

// Refresh notifications
function refreshNotifications() {
    loadNotificationStats();
    loadAllNotifications();
}

// Tab change handlers
document.getElementById('unread-tab').addEventListener('click', loadUnreadNotifications);
document.getElementById('bookings-tab').addEventListener('click', loadBookingNotifications);
document.getElementById('reminders-tab').addEventListener('click', loadReminderNotifications);

// Mark as read button handler
document.getElementById('markAsReadBtn').addEventListener('click', function() {
    if (currentNotificationId) {
        markNotificationAsRead(currentNotificationId);
    }
});

// Utility functions
function getTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
    return `${Math.floor(diffInSeconds / 86400)}d ago`;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>

<style>
.notification-item:hover {
    background-color: #f8f9fa;
    border-radius: 0.375rem;
}

.notification-item {
    transition: all 0.2s ease;
}

.badge {
    font-size: 0.75em;
}

.opacity-75 {
    opacity: 0.75;
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    border-bottom: 2px solid #0d6efd;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}
</style>
<?= $this->endSection() ?>