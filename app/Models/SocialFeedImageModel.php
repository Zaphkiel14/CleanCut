<?php

namespace App\Models;

use CodeIgniter\Model;

class SocialFeedImageModel extends Model
{
    protected $table = 'social_feed_images';
    protected $primaryKey = 'image_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'post_id',
        'image',
        'sort_order',
    ];

    protected $useTimestamps = false;
}


