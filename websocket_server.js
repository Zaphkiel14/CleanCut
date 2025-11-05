const WebSocket = require('ws');
const http = require('http');
const mysql = require('mysql2/promise');

// WebSocket Server for Real-time Communication
class CleanCutWebSocketServer {
    constructor(port = 8080) {
        this.port = port;
        this.clients = new Map(); // Store active connections
        this.server = null;
        this.wss = null;
        this.dbConnection = null;
        
        this.initDatabase();
        this.startServer();
    }

    async initDatabase() {
        try {
            this.dbConnection = await mysql.createConnection({
                host: 'localhost',
                user: 'root',
                password: '',
                database: 'cleancut_db',
                charset: 'utf8mb4'
            });
            console.log('Database connected for WebSocket server');
        } catch (error) {
            console.error('Database connection failed:', error);
        }
    }

    startServer() {
        this.server = http.createServer();
        this.wss = new WebSocket.Server({ server: this.server });

        this.wss.on('connection', (ws, req) => {
            console.log('New WebSocket connection established');
            
            ws.on('message', async (message) => {
                try {
                    const data = JSON.parse(message);
                    await this.handleMessage(ws, data);
                } catch (error) {
                    console.error('Error handling message:', error);
                    ws.send(JSON.stringify({ error: 'Invalid message format' }));
                }
            });

            ws.on('close', () => {
                this.removeClient(ws);
                console.log('WebSocket connection closed');
            });

            ws.on('error', (error) => {
                console.error('WebSocket error:', error);
                this.removeClient(ws);
            });
        });

        this.server.listen(this.port, () => {
            console.log(`WebSocket server running on port ${this.port}`);
        });
    }

    async handleMessage(ws, data) {
        const { type, payload } = data;

        switch (type) {
            case 'auth':
                await this.handleAuth(ws, payload);
                break;
            case 'join_room':
                this.joinRoom(ws, payload);
                break;
            case 'leave_room':
                this.leaveRoom(ws, payload);
                break;
            case 'send_message':
                await this.handleSendMessage(ws, payload);
                break;
            case 'update_appointment_status':
                await this.handleAppointmentStatusUpdate(ws, payload);
                break;
            case 'ping':
                ws.send(JSON.stringify({ type: 'pong' }));
                break;
            default:
                ws.send(JSON.stringify({ error: 'Unknown message type' }));
        }
    }

    async handleAuth(ws, payload) {
        const { userId, token } = payload;
        
        try {
            // Verify user authentication
            const [users] = await this.dbConnection.execute(
                'SELECT user_id, role FROM users WHERE user_id = ? AND is_active = 1',
                [userId]
            );

            if (users.length === 0) {
                ws.send(JSON.stringify({ error: 'Authentication failed' }));
                return;
            }

            const user = users[0];
            this.clients.set(ws, {
                userId: user.user_id,
                role: user.role,
                rooms: new Set(),
                lastPing: Date.now()
            });

            ws.send(JSON.stringify({ 
                type: 'auth_success', 
                payload: { userId: user.user_id, role: user.role }
            }));

            console.log(`User ${user.user_id} authenticated via WebSocket`);
        } catch (error) {
            console.error('Auth error:', error);
            ws.send(JSON.stringify({ error: 'Authentication failed' }));
        }
    }

    joinRoom(ws, payload) {
        const client = this.clients.get(ws);
        if (!client) return;

        const { room } = payload;
        client.rooms.add(room);
        
        ws.send(JSON.stringify({ 
            type: 'room_joined', 
            payload: { room }
        }));
    }

    leaveRoom(ws, payload) {
        const client = this.clients.get(ws);
        if (!client) return;

        const { room } = payload;
        client.rooms.delete(room);
        
        ws.send(JSON.stringify({ 
            type: 'room_left', 
            payload: { room }
        }));
    }

    async handleSendMessage(ws, payload) {
        const client = this.clients.get(ws);
        if (!client) return;

        const { receiverId, message, messageType = 'text' } = payload;

        try {
            // Save message to database
            const [result] = await this.dbConnection.execute(
                'INSERT INTO messages (sender_id, receiver_id, message, message_type, created_at) VALUES (?, ?, ?, ?, NOW())',
                [client.userId, receiverId, message, messageType]
            );

            const messageId = result.insertId;

            // Send to receiver if online
            this.broadcastToUser(receiverId, {
                type: 'new_message',
                payload: {
                    messageId,
                    senderId: client.userId,
                    message,
                    messageType,
                    timestamp: new Date().toISOString()
                }
            });

            // Confirm to sender
            ws.send(JSON.stringify({
                type: 'message_sent',
                payload: { messageId, receiverId }
            }));

        } catch (error) {
            console.error('Send message error:', error);
            ws.send(JSON.stringify({ error: 'Failed to send message' }));
        }
    }

    async handleAppointmentStatusUpdate(ws, payload) {
        const client = this.clients.get(ws);
        if (!client) return;

        const { appointmentId, newStatus, notes } = payload;

        try {
            // Update appointment status
            await this.dbConnection.execute(
                'UPDATE appointments SET status = ?, notes = ?, updated_at = NOW() WHERE appointment_id = ?',
                [newStatus, notes, appointmentId]
            );

            // Get appointment details for notifications
            const [appointments] = await this.dbConnection.execute(`
                SELECT a.*, c.user_id as customer_id, b.user_id as barber_id,
                       c.first_name as customer_name, b.first_name as barber_name
                FROM appointments a
                JOIN users c ON c.user_id = a.customer_id
                JOIN users b ON b.user_id = a.barber_id
                WHERE a.appointment_id = ?
            `, [appointmentId]);

            if (appointments.length > 0) {
                const appointment = appointments[0];
                
                // Notify both customer and barber
                this.broadcastToUser(appointment.customer_id, {
                    type: 'appointment_status_changed',
                    payload: {
                        appointmentId,
                        status: newStatus,
                        barberName: appointment.barber_name,
                        notes
                    }
                });

                this.broadcastToUser(appointment.barber_id, {
                    type: 'appointment_status_changed',
                    payload: {
                        appointmentId,
                        status: newStatus,
                        customerName: appointment.customer_name,
                        notes
                    }
                });

                // If completed, trigger automated earnings calculation
                if (newStatus === 'completed') {
                    await this.triggerEarningsCalculation(appointmentId);
                }
            }

            ws.send(JSON.stringify({
                type: 'appointment_updated',
                payload: { appointmentId, status: newStatus }
            }));

        } catch (error) {
            console.error('Appointment update error:', error);
            ws.send(JSON.stringify({ error: 'Failed to update appointment' }));
        }
    }

    async triggerEarningsCalculation(appointmentId) {
        try {
            // Get appointment details
            const [appointments] = await this.dbConnection.execute(`
                SELECT a.*, s.price, e.shop_id
                FROM appointments a
                JOIN services s ON s.service_id = a.service_id
                JOIN employees e ON e.user_id = a.barber_id
                WHERE a.appointment_id = ?
            `, [appointmentId]);

            if (appointments.length > 0) {
                const appointment = appointments[0];
                
                // Get commission settings
                const [commission] = await this.dbConnection.execute(
                    'SELECT barber_commission_rate FROM commission_settings WHERE shop_id = ?',
                    [appointment.shop_id]
                );

                const commissionRate = commission.length > 0 ? commission[0].barber_commission_rate : 70;
                const commissionAmount = (appointment.price * commissionRate) / 100;

                // Create earnings record
                await this.dbConnection.execute(`
                    INSERT INTO earnings (barber_id, appointment_id, service_id, amount, 
                                       commission_rate, commission_amount, payment_method, 
                                       earning_date, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, 'cash', ?, NOW())
                `, [
                    appointment.barber_id, appointmentId, appointment.service_id,
                    appointment.price, commissionRate, commissionAmount, appointment.appointment_date
                ]);

                // Notify barber of earnings
                this.broadcastToUser(appointment.barber_id, {
                    type: 'earnings_updated',
                    payload: {
                        appointmentId,
                        amount: appointment.price,
                        commission: commissionAmount,
                        date: appointment.appointment_date
                    }
                });

                console.log(`Earnings calculated for appointment ${appointmentId}`);
            }
        } catch (error) {
            console.error('Earnings calculation error:', error);
        }
    }

    broadcastToUser(userId, message) {
        for (const [ws, client] of this.clients) {
            if (client.userId === userId && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify(message));
            }
        }
    }

    broadcastToRoom(room, message) {
        for (const [ws, client] of this.clients) {
            if (client.rooms.has(room) && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify(message));
            }
        }
    }

    broadcastToAll(message) {
        for (const [ws] of this.clients) {
            if (ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify(message));
            }
        }
    }

    removeClient(ws) {
        this.clients.delete(ws);
    }

    // Automated appointment reminders
    async sendAppointmentReminders() {
        try {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const tomorrowStr = tomorrow.toISOString().split('T')[0];

            const [appointments] = await this.dbConnection.execute(`
                SELECT a.*, c.user_id as customer_id, b.user_id as barber_id,
                       c.first_name as customer_name, b.first_name as barber_name,
                       s.service_name
                FROM appointments a
                JOIN users c ON c.user_id = a.customer_id
                JOIN users b ON b.user_id = a.barber_id
                JOIN services s ON s.service_id = a.service_id
                WHERE a.appointment_date = ? AND a.status = 'confirmed'
            `, [tomorrowStr]);

            for (const appointment of appointments) {
                // Send reminder to customer
                this.broadcastToUser(appointment.customer_id, {
                    type: 'appointment_reminder',
                    payload: {
                        appointmentId: appointment.appointment_id,
                        barberName: appointment.barber_name,
                        serviceName: appointment.service_name,
                        date: appointment.appointment_date,
                        time: appointment.appointment_time
                    }
                });

                // Send reminder to barber
                this.broadcastToUser(appointment.barber_id, {
                    type: 'appointment_reminder',
                    payload: {
                        appointmentId: appointment.appointment_id,
                        customerName: appointment.customer_name,
                        serviceName: appointment.service_name,
                        date: appointment.appointment_date,
                        time: appointment.appointment_time
                    }
                });
            }

            console.log(`Sent ${appointments.length} appointment reminders`);
        } catch (error) {
            console.error('Reminder error:', error);
        }
    }

    // Auto-complete appointments that are past their time
    async autoCompleteAppointments() {
        try {
            const now = new Date();
            const oneHourAgo = new Date(now.getTime() - 60 * 60 * 1000);

            const [appointments] = await this.dbConnection.execute(`
                SELECT appointment_id, barber_id, customer_id
                FROM appointments
                WHERE status = 'confirmed' 
                AND CONCAT(appointment_date, ' ', appointment_time) < ?
            `, [oneHourAgo.toISOString().slice(0, 19)]);

            for (const appointment of appointments) {
                await this.handleAppointmentStatusUpdate(null, {
                    appointmentId: appointment.appointment_id,
                    newStatus: 'completed',
                    notes: 'Auto-completed - service time passed'
                });
            }

            if (appointments.length > 0) {
                console.log(`Auto-completed ${appointments.length} appointments`);
            }
        } catch (error) {
            console.error('Auto-complete error:', error);
        }
    }
}

// Start the server
const server = new CleanCutWebSocketServer(8080);

// Set up automated tasks
setInterval(() => {
    server.sendAppointmentReminders();
}, 24 * 60 * 60 * 1000); // Daily reminders

setInterval(() => {
    server.autoCompleteAppointments();
}, 60 * 60 * 1000); // Hourly auto-completion

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('Shutting down WebSocket server...');
    server.server.close();
    process.exit(0);
});

module.exports = CleanCutWebSocketServer;
