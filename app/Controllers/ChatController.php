<?php

namespace App\Controllers;

use Exception;
use App\Models\UserModel;
use App\Models\MessageModel;

class ChatController extends BaseController
{
    protected $messageModel;
    protected $userModel;

    public function __construct()
    {
        $this->messageModel = new MessageModel();
        $this->userModel = new UserModel();
    }

    // Show chat interface
    public function index()
    {
        $userId = session()->get('user_id');
        $userRole = session()->get('user_role');

        // Get users to chat with based on role
        if ($userRole === 'customer') {
            $users = $this->userModel->getBarbers();
        } elseif ($userRole === 'barber') {
            $users = $this->userModel->getCustomers();
        } else {
            $users = [];
        }

        $data = [
            'title' => 'Messages',
            'users' => $users,
            'user_role' => $userRole,
        ];

        return view('chat/index', $data);
    }



    // Send message
    public function sendMessage()
    {
        $senderId = session()->get('user_id');
        if (!$senderId) {
            return $this->response->setJSON(['error' => 'Not logged in'])->setStatusCode(401);
        }

        $receiverId = $this->request->getPost('receiver_id');
        $message = $this->request->getPost('message');

        if (!$receiverId || !$message) {
            return $this->response->setJSON(['error' => 'Missing data'])->setStatusCode(400);
        }

        // Create table if needed
        $db = \Config\Database::connect();
        $db->query("CREATE TABLE IF NOT EXISTS messages (
            message_id INT AUTO_INCREMENT PRIMARY KEY,
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Insert message
        $db->query(
            "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)",
            [$senderId, $receiverId, $message]
        );

        // Return success
        return $this->response->setJSON(['success' => true]);
    }

    // Send message (alias for sendMessage)
    public function send()
    {
        return $this->sendMessage();
    }

    // Send message via GET request (simple approach)
    public function sendMessageGet()
    {
        $senderId = session()->get('user_id');
        if (!$senderId) {
            return redirect()->to('/login');
        }

        $receiverId = $this->request->getGet('receiver_id');
        $message = $this->request->getGet('message');

        if (!$receiverId || !$message) {
            return redirect()->back()->with('error', 'Missing required parameters');
        }

        // Validate that receiver exists
        $receiver = $this->userModel->find($receiverId);
        if (!$receiver) {
            return redirect()->back()->with('error', 'User not found');
        }

        try {
            $messageId = $this->messageModel->sendMessage($senderId, $receiverId, $message, 'text');

            if ($messageId) {
                return redirect()->to('chat/conversation/' . $receiverId)->with('success', 'Message sent successfully');
            } else {
                return redirect()->back()->with('error', 'Failed to send message');
            }
        } catch (Exception $e) {
            log_message('error', 'Chat send error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Internal server error');
        }
    }

    // Get conversation messages
    public function conversation($otherUserId, $action = null)
    {
        $userId = session()->get('user_id');

        if ($action === 'messages') {
            // Return messages as JSON for AJAX
            $messages = $this->messageModel->getConversation($userId, $otherUserId);
            return $this->response->setJSON([
                'success' => true,
                'messages' => $messages
            ]);
        }

        // Show conversation page
        $otherUser = $this->userModel->find($otherUserId);
        if (!$otherUser) {
            return redirect()->to('/chat')->with('error', 'User not found');
        }

        // Get messages directly
        $db = \Config\Database::connect();
        $sql = "SELECT * FROM messages 
                WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) 
                ORDER BY created_at ASC";
        $messages = $db->query($sql, [$userId, $otherUserId, $otherUserId, $userId])->getResultArray();

        // Mark as read
        $db->query("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?", [$otherUserId, $userId]);

        $data = [
            'title' => 'Chat with ' . $otherUser['first_name'],
            'other_user' => $otherUser,
            'messages' => $messages,
        ];

        return view('chat/conversation', $data);
    }

    // Get recent conversations
    public function getRecentConversations()
    {
        $userId = session()->get('user_id');
        $conversations = $this->messageModel->getRecentConversations($userId);

        return $this->response->setJSON([
            'success' => true,
            'conversations' => $conversations
        ]);
    }

    // Get recent conversations (alias)
    public function recent()
    {
        return $this->getRecentConversations();
    }

    // Get unread count
    public function getUnreadCount()
    {
        $userId = session()->get('user_id');
        $unreadCount = $this->messageModel->getUnreadCount($userId);

        return $this->response->setJSON([
            'success' => true,
            'unread_count' => $unreadCount
        ]);
    }

    // Get conversation messages
    public function getConversation($otherUserId)
    {
        $userId = session()->get('user_id');
        $messages = $this->messageModel->getConversation($userId, $otherUserId);

        // Mark messages as read
        $this->messageModel->markAsRead($otherUserId, $userId);

        return $this->response->setJSON([
            'success' => true,
            'messages' => $messages
        ]);
    }

    // Upload file/image
    public function uploadFile()
    {
        $file = $this->request->getFile('file');

        if (!$file->isValid()) {
            return $this->response->setJSON([
                'error' => 'Invalid file'
            ]);
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];

        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return $this->response->setJSON([
                'error' => 'File type not allowed'
            ]);
        }

        $newName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads/chat', $newName);

        $filePath = 'uploads/chat/' . $newName;
        $messageType = strpos($file->getMimeType(), 'image/') === 0 ? 'image' : 'file';

        return $this->response->setJSON([
            'success' => true,
            'file_path' => $filePath,
            'message_type' => $messageType,
            'file_name' => $file->getClientName()
        ]);
    }

    // Upload file (alias)
    public function upload()
    {
        return $this->uploadFile();
    }

    // Delete conversation
    public function deleteConversation($otherUserId)
    {
        $userId = session()->get('user_id');

        $result = $this->messageModel->deleteConversation($userId, $otherUserId);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Conversation deleted successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'error' => 'Failed to delete conversation'
            ]);
        }
    }

    // Get online users
    public function getOnlineUsers()
    {
        $userId = session()->get('user_id');
        $onlineUsers = $this->messageModel->getOnlineUsers($userId);

        return $this->response->setJSON([
            'success' => true,
            'online_users' => $onlineUsers
        ]);
    }

    // Get online users (alias)
    public function onlineUsers()
    {
        return $this->getOnlineUsers();
    }

    // Search users
    public function searchUsers()
    {
        $keyword = $this->request->getGet('keyword');
        $userRole = session()->get('user_role');

        if (!$keyword) {
            return $this->response->setJSON([
                'error' => 'Search keyword required'
            ]);
        }

        $searchRole = $userRole === 'customer' ? 'barber' : 'customer';

        $users = $this->userModel->like('first_name', $keyword)
            ->orLike('last_name', $keyword)
            ->where('role', $searchRole)
            ->where('is_active', 1)
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'users' => $users
        ]);
    }

    // Mark messages as read
    public function markAsRead()
    {
        $userId = session()->get('user_id');
        $senderId = $this->request->getPost('sender_id');

        if (!$senderId) {
            return $this->response->setJSON([
                'error' => 'Sender ID required'
            ]);
        }

        $result = $this->messageModel->markAsRead($senderId, $userId);

        return $this->response->setJSON([
            'success' => true,
            'result' => $result
        ]);
    }

    // Get chat notifications
    public function getNotifications()
    {
        $userId = session()->get('user_id');
        $unreadMessages = $this->messageModel->getUnreadMessages($userId);

        $notifications = [];
        foreach ($unreadMessages as $message) {
            $sender = $this->userModel->find($message['sender_id']);
            $notifications[] = [
                'message_id' => $message['message_id'],
                'sender_name' => $sender['first_name'] . ' ' . $sender['last_name'],
                'message' => substr($message['message'], 0, 50) . '...',
                'created_at' => $message['created_at']
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'notifications' => $notifications
        ]);
    }
}
