<?php

namespace App\Controllers;

use App\Models\MessageModel;
use App\Models\UserModel;

class ChatTestController extends BaseController
{
    protected $messageModel;
    protected $userModel;

    public function __construct()
    {
        $this->messageModel = new MessageModel();
        $this->userModel = new UserModel();
    }

    // Test chat functionality
    public function test()
    {
        $data = [
            'title' => 'Chat Test',
            'message' => 'Chat system is working!',
            'timestamp' => date('Y-m-d H:i:s')
        ];

        return view('chat/test', $data);
    }

    // Test message sending
    public function testSend()
    {
        $senderId = session()->get('user_id');
        if (!$senderId) {
            return $this->response->setJSON(['error' => 'Not logged in']);
        }

        $receiverId = $this->request->getPost('receiver_id') ?? 1; // Default to user ID 1
        $message = $this->request->getPost('message') ?? 'Test message';

        try {
            $messageId = $this->messageModel->sendMessage($senderId, $receiverId, $message);
            
            if ($messageId) {
                return $this->response->setJSON([
                    'success' => true,
                    'message_id' => $messageId,
                    'message' => 'Test message sent successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'error' => 'Failed to send message',
                    'debug' => [
                        'sender_id' => $senderId,
                        'receiver_id' => $receiverId,
                        'message' => $message
                    ]
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => 'Exception occurred',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    // Test database connection
    public function testDb()
    {
        try {
            $db = \Config\Database::connect();
            
            // Test basic query
            $result = $db->query('SELECT COUNT(*) as count FROM users')->getRow();
            
            return $this->response->setJSON([
                'success' => true,
                'database' => 'Connected',
                'user_count' => $result->count,
                'message_table_exists' => $db->tableExists('messages')
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => 'Database error',
                'message' => $e->getMessage()
            ]);
        }
    }

    // Get recent messages for testing
    public function getRecentMessages()
    {
        try {
            $userId = session()->get('user_id');
            if (!$userId) {
                return $this->response->setJSON(['error' => 'Not logged in']);
            }

            $messages = $this->messageModel->getRecentConversations($userId, 5);
            
            return $this->response->setJSON([
                'success' => true,
                'messages' => $messages,
                'count' => count($messages)
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => 'Failed to get messages',
                'message' => $e->getMessage()
            ]);
        }
    }

    // Debug session and user data
    public function debug()
    {
        $data = [
            'session_data' => session()->get(),
            'user_id' => session()->get('user_id'),
            'user_role' => session()->get('user_role'),
            'is_logged_in' => session()->get('is_logged_in'),
            'timestamp' => date('Y-m-d H:i:s')
        ];

        return $this->response->setJSON($data);
    }
}

