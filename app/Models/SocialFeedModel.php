<?php

namespace App\Models;

use CodeIgniter\Model;

class SocialFeedModel extends Model
{
    protected $table = 'social_feed';
    protected $primaryKey = 'post_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'post_type',
        'title',
        'content',
        'images',
        'status',
        'is_public',
        'likes_count',
        'created_at',
        'updated_at',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer',
        'title' => 'permit_empty|min_length[2]|max_length[255]',
        'content' => 'permit_empty|min_length[2]'
    ];

    protected $validationMessages = [
        'title' => [
            'required' => 'Title is required.'
        ],
        'content' => [
            'required' => 'Content is required.'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Helper methods
    public function getPublicFeed($limit = 20)
    {
        return $this->select('social_feed.*, users.first_name, users.last_name, users.profile_picture AS profile_picture')
                    ->join('users', 'users.user_id = social_feed.user_id', 'left')
                    ->where('is_public', 1)
                    ->orderBy('social_feed.created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    public function getUserPosts($userId, $limit = 20)
    {
        return $this->select('social_feed.*, users.first_name, users.last_name, users.profile_picture AS profile_picture')
                    ->join('users', 'users.user_id = social_feed.user_id', 'left')
                    ->where('social_feed.user_id', $userId)
                    ->orderBy('social_feed.created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    public function getWorkShowcase($limit = 20)
    {
        return $this->select('social_feed.*, users.first_name, users.last_name, users.profile_picture AS profile_picture')
                    ->join('users', 'users.user_id = social_feed.user_id', 'left')
                    ->where('social_feed.is_public', 1)
                    ->where('social_feed.post_type', 'work_showcase')
                    ->orderBy('social_feed.created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    public function getStatusUpdates($limit = 20)
    {
        return $this->select('social_feed.*, users.first_name, users.last_name, users.profile_picture AS profile_picture')
                    ->join('users', 'users.user_id = social_feed.user_id', 'left')
                    ->where('social_feed.post_type', 'status_update')
                    ->orderBy('social_feed.created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    public function getPostWithDetails($postId)
    {
        return $this->find($postId);
    }

    public function searchPosts($search, $limit = 20)
    {
        return $this->select('social_feed.*, users.first_name, users.last_name, users.profile_picture AS profile_picture')
                    ->join('users', 'users.user_id = social_feed.user_id', 'left')
                    ->groupStart()
                        ->like('social_feed.title', $search)
                        ->orLike('social_feed.content', $search)
                    ->groupEnd()
                    ->where('social_feed.is_public', 1)
                    ->orderBy('social_feed.created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    public function createPost($userId, $title, $content, $imagesJson = null)
    {
        $data = [
            'user_id' => $userId,
            'title' => $title,
            'content' => $content,
            'images' => $imagesJson,
            'is_public' => 1
        ];
        return $this->insert($data);
    }

    public function updatePost($postId, $data)
    {
        return $this->update($postId, $data);
    }

    public function deletePost($postId)
    {
        return $this->delete($postId);
    }

    public function likePost(int $postId): bool
    {
        return (bool) $this->set('likes_count', 'likes_count + 1', false)
                           ->where('post_id', $postId)
                           ->update();
    }

    public function unlikePost(int $postId): bool
    {
        $post = $this->find($postId);
        if (!$post) {
            return false;
        }
        $newCount = max(0, (int) $post['likes_count'] - 1);
        return (bool) $this->update($postId, ['likes_count' => $newCount]);
    }

    public function updateBarberStatus(int $userId, string $status, ?string $content = null): ?int
    {
        $existing = $this->where('user_id', $userId)
                         ->where('post_type', 'status_update')
                         ->orderBy('created_at', 'DESC')
                         ->first();

        $data = [
            'user_id' => $userId,
            'post_type' => 'status_update',
            'status' => $status,
            'content' => $content,
            'is_public' => 1,
        ];

        if (is_array($existing) && !empty($existing)) {
            $this->update((int) $existing['post_id'], $data);
            return (int) $existing['post_id'];
        }

        $this->insert($data);
        return (int) $this->getInsertID();
    }

    public function getBarberStatus(int $barberId): ?string
    {
        $row = $this->select('status')
                    ->where('user_id', $barberId)
                    ->where('post_type', 'status_update')
                    ->orderBy('created_at', 'DESC')
                    ->first();
        return $row['status'] ?? null;
    }
}
