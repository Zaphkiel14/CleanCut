// WebSocket Client for Real-time Communication
class CleanCutWebSocketClient {
    constructor() {
        this.ws = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 1000;
        this.isConnected = false;
        this.userId = null;
        this.role = null;
        this.messageHandlers = new Map();
        
        this.init();
    }

    init() {
        this.connect();
        this.setupEventListeners();
    }

    connect() {
        try {
            this.ws = new WebSocket('ws://localhost:8080');
            
            this.ws.onopen = () => {
                console.log('WebSocket connected');
                this.isConnected = true;
                this.reconnectAttempts = 0;
                this.authenticate();
            };

            this.ws.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    this.handleMessage(data);
                } catch (error) {
                    console.error('Error parsing WebSocket message:', error);
                }
            };

            this.ws.onclose = () => {
                console.log('WebSocket disconnected');
                this.isConnected = false;
                this.reconnect();
            };

            this.ws.onerror = (error) => {
                console.error('WebSocket error:', error);
            };

        } catch (error) {
            console.error('Failed to connect to WebSocket:', error);
            this.reconnect();
        }
    }

    reconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`Attempting to reconnect... (${this.reconnectAttempts}/${this.maxReconnectAttempts})`);
            
            setTimeout(() => {
                this.connect();
            }, this.reconnectDelay * this.reconnectAttempts);
        } else {
            console.error('Max reconnection attempts reached');
        }
    }

    authenticate() {
        const userId = this.getUserId();
        if (userId) {
            this.send({
                type: 'auth',
                payload: { userId: userId }
            });
        }
    }

    getUserId() {
        // Get user ID from session or cookie
        const userIdElement = document.querySelector('[data-user-id]');
        return userIdElement ? userIdElement.dataset.userId : null;
    }

    send(data) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify(data));
        } else {
            console.warn('WebSocket not connected, cannot send message');
        }
    }

    handleMessage(data) {
        const { type, payload, error } = data;

        if (error) {
            console.error('WebSocket error:', error);
            return;
        }

        switch (type) {
            case 'auth_success':
                this.userId = payload.userId;
                this.role = payload.role;
                this.joinUserRooms();
                break;
            case 'new_message':
                this.handleNewMessage(payload);
                break;
            case 'appointment_status_changed':
                this.handleAppointmentStatusChange(payload);
                break;
            case 'appointment_reminder':
                this.handleAppointmentReminder(payload);
                break;
            case 'earnings_updated':
                this.handleEarningsUpdate(payload);
                break;
            case 'notification':
                this.handleNotification(payload);
                break;
            default:
                console.log('Unknown message type:', type);
        }

        // Call registered handlers
        if (this.messageHandlers.has(type)) {
            this.messageHandlers.get(type).forEach(handler => {
                try {
                    handler(payload);
                } catch (error) {
                    console.error('Error in message handler:', error);
                }
            });
        }
    }

    joinUserRooms() {
        if (this.userId) {
            this.send({
                type: 'join_room',
                payload: { room: `user_${this.userId}` }
            });
            
            if (this.role) {
                this.send({
                    type: 'join_room',
                    payload: { room: `role_${this.role}` }
                });
            }
        }
    }

    handleNewMessage(payload) {
        // Update chat interface
        if (window.updateChatMessages) {
            window.updateChatMessages(payload);
        }
        
        // Show notification
        this.showNotification('New Message', payload.message);
        
        // Update unread count
        this.updateUnreadCount();
    }

    handleAppointmentStatusChange(payload) {
        // Update appointment status in UI
        if (window.updateAppointmentStatus) {
            window.updateAppointmentStatus(payload);
        }
        
        // Show notification
        this.showNotification('Appointment Update', `Status changed to ${payload.status}`);
    }

    handleAppointmentReminder(payload) {
        this.showNotification('Appointment Reminder', 
            `You have an appointment tomorrow at ${payload.time}`);
    }

    handleEarningsUpdate(payload) {
        // Update earnings display
        if (window.updateEarnings) {
            window.updateEarnings(payload);
        }
        
        this.showNotification('Earnings Updated', 
            `Commission: ₱${payload.commission.toFixed(2)}`);
    }

    handleNotification(payload) {
        this.showNotification(payload.title, payload.message);
    }

    showNotification(title, message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'toast-notification';
        notification.innerHTML = `
            <div class="toast-header">
                <strong>${title}</strong>
                <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
            <div class="toast-body">${message}</div>
        `;
        
        // Add to page
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    updateUnreadCount() {
        // Update unread message count
        fetch('/notifications/count')
            .then(response => response.json())
            .then(data => {
                const badge = document.querySelector('.notification-badge');
                if (badge) {
                    badge.textContent = data.count;
                    badge.style.display = data.count > 0 ? 'inline' : 'none';
                }
            });
    }

    // Public methods for sending messages
    sendMessage(receiverId, message, messageType = 'text') {
        this.send({
            type: 'send_message',
            payload: { receiverId, message, messageType }
        });
    }

    updateAppointmentStatus(appointmentId, status, notes = '') {
        this.send({
            type: 'update_appointment_status',
            payload: { appointmentId, newStatus: status, notes }
        });
    }

    // Register message handlers
    onMessage(type, handler) {
        if (!this.messageHandlers.has(type)) {
            this.messageHandlers.set(type, []);
        }
        this.messageHandlers.get(type).push(handler);
    }

    // Ping to keep connection alive
    startPing() {
        setInterval(() => {
            if (this.isConnected) {
                this.send({ type: 'ping' });
            }
        }, 30000);
    }
}

// Initialize WebSocket client when page loads
document.addEventListener('DOMContentLoaded', function() {
    window.wsClient = new CleanCutWebSocketClient();
    window.wsClient.startPing();
});

// Export for use in other scripts
window.CleanCutWebSocketClient = CleanCutWebSocketClient;
