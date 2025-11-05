<?php
namespace App\Models;

use CodeIgniter\Model;

class FollowModel extends Model {
    protected $table = 'follows';
    protected $primaryKey = 'id';
    protected $allowedFields = ['follower_id', 'followed_id', 'created_at'];
    public $timestamps = true;

    public function isFollowing($followerId, $followedId) {
        return $this->where(['follower_id'=> $followerId,'followed_id'=> $followedId])->countAllResults() > 0;
    }
    public function follow($followerId, $followedId) {
        if (!$this->isFollowing($followerId, $followedId) && $followerId !== $followedId) {
            return $this->insert([
                'follower_id' => $followerId,
                'followed_id' => $followedId
            ]);
        }
        return false;
    }
    public function unfollow($followerId, $followedId) {
        return $this->where(['follower_id'=> $followerId, 'followed_id'=> $followedId])->delete();
    }
    public function getFollowingIds($followerId) {
        return array_column(
            $this->select('followed_id')->where('follower_id', $followerId)->findAll(),
            'followed_id'
        );
    }
}

