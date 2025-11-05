<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <!-- Back Button -->
        <div class="col-12 mb-3">
            <a href="<?= base_url('chat') ?>" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Messages
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Chat Header -->
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                            <?= strtoupper(substr($other_user['first_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h5 class="mb-0"><?= esc($other_user['first_name'] . ' ' . $other_user['last_name']) ?></h5>
                            <small>Online</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Messages Area -->
        <div class="col-12">
            <div class="card">
                <div class="card-body" style="height: 400px; overflow-y: auto;" id="messagesContainer">
                    <?php if (!empty($messages)): ?>
                        <?php foreach ($messages as $message): ?>
                            <div class="message mb-3 <?= $message['sender_id'] == session()->get('user_id') ? 'text-end' : 'text-start' ?>" data-message-id="<?= (int)$message['message_id'] ?>">
                                <div class="d-inline-block p-3 rounded <?= $message['sender_id'] == session()->get('user_id') ? 'bg-primary text-white' : 'bg-light' ?>" style="max-width: 70%;">
                                    <?php if ($message['message_type'] === 'text'): ?>
                                        <p class="mb-0"><?= esc($message['message']) ?></p>
                                    <?php elseif ($message['message_type'] === 'image'): ?>
                                        <img src="<?= base_url($message['message']) ?>" alt="Image" class="img-fluid rounded" style="max-width: 200px;">
                                    <?php else: ?>
                                        <a href="<?= base_url($message['message']) ?>" class="text-decoration-none">
                                            <i class="fas fa-file"></i> Download File
                                        </a>
                                    <?php endif; ?>
                                    <small class="d-block mt-1 opacity-75">
                                        <?= date('M j, g:i A', strtotime($message['created_at'])) ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-comment-dots fa-3x mb-3"></i>
                            <p>No messages yet. Start the conversation!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Input -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" id="messageInput" class="form-control" placeholder="Type your message..." required>
                        <button type="button" id="sendButton" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-sm {
        width: 40px;
        height: 40px;
        font-size: 16px;
        font-weight: bold;
    }

    .message .bg-primary {
        background-color: #007bff !important;
    }

    .message .bg-light {
        background-color: #f8f9fa !important;
    }

    #messagesContainer {
        scroll-behavior: smooth;
    }

    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Simplified chat functionality that works reliably
        let currentConversation = <?= $other_user['user_id'] ?? 'null' ?>;
        let messageContainer = document.getElementById('messagesContainer');
        let messageInput = document.getElementById('messageInput');
        let sendButton = document.getElementById('sendButton');
        let lastMessageId = getLastMessageId();

        // Auto-scroll to bottom
        scrollToBottom();

        // Handle message sending
        function sendMessage() {
            const message = messageInput.value.trim();
            if (!message) return;

            // Disable input while sending
            messageInput.disabled = true;
            sendButton.disabled = true;
            sendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

            // Send via AJAX
            fetch('<?= base_url('chat/send') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `receiver_id=${currentConversation}&message=${encodeURIComponent(message)}&<?= csrf_token() ?>=<?= csrf_hash() ?>`
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);

                    if (data.success) {
                        // Clear input and fetch the authoritative message list
                        messageInput.value = '';
                        fetchNewMessages();

                        // Show success message
                        showNotification('Message sent successfully!', 'success');
                    } else {
                        console.error('Server error:', data.error);
                        showNotification('Failed to send message: ' + (data.error || 'Unknown error'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error sending message:', error);
                    showNotification('Network error: ' + error.message, 'error');
                })
                .finally(() => {
                    // Re-enable input
                    messageInput.disabled = false;
                    sendButton.disabled = false;
                    sendButton.innerHTML = '<i class="fas fa-paper-plane"></i> Send';
                });
        }

        function addMessageToUI(message, isOwn) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message mb-3 ${isOwn ? 'text-end' : 'text-start'}`;

            const now = new Date();
            const timeString = now.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });

            messageDiv.innerHTML = `
            <div class="d-inline-block p-3 rounded ${isOwn ? 'bg-primary text-white' : 'bg-light'}" style="max-width: 70%;">
                <p class="mb-0">${escapeHtml(message)}</p>
                <small class="d-block mt-1 opacity-75">${timeString}</small>
            </div>
        `;

            messageContainer.appendChild(messageDiv);
            scrollToBottom();
        }

        function addServerMessageToUI(msg) {
            const isOwn = String(msg.sender_id) === String(<?= (int)session()->get('user_id') ?>);
            const messageDiv = document.createElement('div');
            messageDiv.className = `message mb-3 ${isOwn ? 'text-end' : 'text-start'}`;
            messageDiv.setAttribute('data-message-id', msg.message_id);

            const createdAt = new Date(msg.created_at);
            const timeString = createdAt.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });

            let innerHtml = '';
            if (msg.message_type === 'image') {
                innerHtml = `<img src="${msg.message}" alt="Image" class="img-fluid rounded" style="max-width: 200px;">`;
            } else if (msg.message_type && msg.message_type !== 'text') {
                innerHtml = `<a href="${msg.message}" class="text-decoration-none"><i class="fas fa-file"></i> Download File</a>`;
            } else {
                innerHtml = `<p class="mb-0">${escapeHtml(msg.message || '')}</p>`;
            }

            messageDiv.innerHTML = `
            <div class="d-inline-block p-3 rounded ${isOwn ? 'bg-primary text-white' : 'bg-light'}" style="max-width: 70%;">
                ${innerHtml}
                <small class="d-block mt-1 opacity-75">${timeString}</small>
            </div>`;

            messageContainer.appendChild(messageDiv);
        }

        function getLastMessageId() {
            const items = messageContainer.querySelectorAll('.message[data-message-id]');
            if (!items.length) return 0;
            const last = items[items.length - 1];
            return parseInt(last.getAttribute('data-message-id')) || 0;
        }

        let isFetching = false;
        function fetchNewMessages() {
            if (isFetching || !currentConversation) return;
            if (document.visibilityState !== 'visible') return;
            isFetching = true;

            fetch('<?= base_url('chat/conversation') ?>/' + currentConversation + '/messages', {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.ok ? r.json() : Promise.reject(new Error('Failed to load messages')))
            .then(data => {
                if (!data || !data.success || !Array.isArray(data.messages)) return;

                const previousLastId = lastMessageId;
                const newOnes = data.messages.filter(m => {
                    const id = Number(m.message_id);
                    if (id <= Number(previousLastId)) return false;
                    // Skip if already in DOM
                    return !messageContainer.querySelector(`[data-message-id="${id}"]`);
                });
                if (newOnes.length) {
                    const atBottom = (messageContainer.scrollTop + messageContainer.clientHeight + 20) >= messageContainer.scrollHeight;
                    newOnes.forEach(m => addServerMessageToUI(m));
                    lastMessageId = getLastMessageId();
                    if (atBottom) scrollToBottom();
                }
            })
            .catch(err => {
                // Optional: log silently
                console.debug('Fetch messages error:', err.message);
            })
            .finally(() => { isFetching = false; });
        }

        function scrollToBottom() {
            messageContainer.scrollTop = messageContainer.scrollHeight;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'info'} position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <span>${message}</span>
                <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;

            document.body.appendChild(notification);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }

        // Event listeners
        sendButton.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Poll for new messages every 2 seconds (AJAX, no page reload)
        setInterval(fetchNewMessages, 2000);

        // Test WebSocket connection (optional)
        if (window.wsClient) {
            console.log('WebSocket client available');
            window.wsClient.onMessage('new_message', function(messageData) {
                if (messageData.senderId == currentConversation) {
                    addMessageToUI(messageData.message, false);
                    showNotification('New message received!', 'info');
                }
            });
        } else {
            console.log('WebSocket client not available, using AJAX fallback');
        }
    });
</script>
<?= $this->endSection() ?>