<?php

namespace App\Models;

use CodeIgniter\Model;

class SocialFeedCommentModel extends Model
{
    protected $table = 'social_feed_comments';
    protected $primaryKey = 'comment_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'post_id',
        'user_id',
        'parent_comment_id',
        'content',
        'likes_count',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getCommentsForPost(int $postId, int $limit = 50): array
    {
        return $this->select('social_feed_comments.*, users.first_name, users.last_name, users.profile_picture')
            ->join('users', 'users.user_id = social_feed_comments.user_id', 'left')
            ->where('social_feed_comments.post_id', $postId)
            ->orderBy('social_feed_comments.created_at', 'ASC')
            ->limit($limit)
            ->findAll();
    }
}



