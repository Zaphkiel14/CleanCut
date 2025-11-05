<?php

namespace App\Models;

use CodeIgniter\Model;

class MessageModel extends Model
{
    protected $table = 'messages';
    protected $primaryKey = 'message_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'sender_id',
        'receiver_id',
        'subject',
        'message',
        'message_type',
        'is_read'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = false; // No updated_at field

    // Validation
    protected $validationRules = [
        'sender_id' => 'required|integer',
        'receiver_id' => 'required|integer',
        'message' => 'required|min_length[1]',
        'subject' => 'permit_empty|max_length[255]'
    ];

    protected $validationMessages = [
        'sender_id' => [
            'required' => 'Sender is required.'
        ],
        'receiver_id' => [
            'required' => 'Receiver is required.'
        ],
        'message' => [
            'required' => 'Message content is required.'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Helper methods
    public function getConversation($user1Id, $user2Id, $limit = 50)
    {
        $db = \Config\Database::connect();

        $sql = "SELECT * FROM messages 
                WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) 
                ORDER BY created_at ASC 
                LIMIT ?";

        $result = $db->query($sql, [$user1Id, $user2Id, $user2Id, $user1Id, $limit]);
        return $result->getResultArray();
    }

    public function getUnreadMessages($userId)
    {
        return $this->where('receiver_id', $userId)
            ->where('is_read', 0)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    public function getRecentConversations($userId, $limit = 10)
    {
        $db = \Config\Database::connect();

        $sql = "SELECT m1.*, u.first_name, u.last_name, u.profile_picture
                FROM messages m1
                JOIN users u ON u.user_id = CASE WHEN m1.sender_id = ? THEN m1.receiver_id ELSE m1.sender_id END
                WHERE m1.message_id IN (
                    SELECT MAX(message_id) 
                    FROM messages 
                    WHERE sender_id = ? OR receiver_id = ? 
                    GROUP BY CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END
                )
                ORDER BY m1.created_at DESC
                LIMIT ?";

        $result = $db->query($sql, [$userId, $userId, $userId, $userId, $limit]);
        return $result->getResultArray();
    }

    // Mark messages as read by sender and receiver
    public function markAsRead($senderId, $receiverId)
    {
        $db = \Config\Database::connect();
        return $db->table($this->table)
            ->where('sender_id', $senderId)
            ->where('receiver_id', $receiverId)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);
    }

    public function markConversationAsRead($user1Id, $user2Id)
    {
        $db = \Config\Database::connect();
        return $db->table($this->table)
            ->where('sender_id', $user2Id)
            ->where('receiver_id', $user1Id)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);
    }

    public function getUnreadCount($userId)
    {
        return $this->where('receiver_id', $userId)
            ->where('is_read', 0)
            ->countAllResults();
    }

    public function sendMessage($senderId, $receiverId, $message, $messageType = 'text', $subject = null)
    {
        try {
            $db = \Config\Database::connect();
            
            $sql = "INSERT INTO messages (sender_id, receiver_id, message, message_type, subject, is_read, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            log_message('debug', 'Inserting message with params: ' . json_encode([$senderId, $receiverId, $message, $messageType, $subject, 0]));
            
            $result = $db->query($sql, [$senderId, $receiverId, $message, $messageType, $subject, 0]);
            
            if ($result) {
                $insertId = $db->insertID();
                log_message('debug', 'Message inserted successfully with ID: ' . $insertId);
                return $insertId;
            } else {
                $error = $db->error();
                log_message('error', 'Failed to insert message: ' . json_encode($error));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'MessageModel sendMessage error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine());
            return false;
        }
    }

    public function getMessageWithSender($messageId)
    {
        try {
            $db = \Config\Database::connect();

            $result = $db->table('messages m')
                ->select('m.*, u.first_name, u.last_name, u.profile_picture')
                ->join('users u', 'u.user_id = m.sender_id')
                ->where('m.message_id', $messageId)
                ->get();

            return $result->getRowArray();
        } catch (\Exception $e) {
            log_message('error', 'getMessageWithSender error: ' . $e->getMessage());
            return null;
        }
    }

    public function deleteConversation($user1Id, $user2Id)
    {
        $db = \Config\Database::connect();

        $sql = "DELETE FROM messages 
                WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)";

        return $db->query($sql, [$user1Id, $user2Id, $user2Id, $user1Id]);
    }

    public function getOnlineUsers($currentUserId)
    {
        // This is a simplified version - in a real app you'd track online status
        $userModel = new \App\Models\UserModel();
        return $userModel->where('user_id !=', $currentUserId)
            ->where('is_active', 1)
            ->findAll();
    }
}
