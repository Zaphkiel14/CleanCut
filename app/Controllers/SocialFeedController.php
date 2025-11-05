<?php

namespace App\Controllers;

use App\Models\SocialFeedModel;
use App\Models\UserModel;
use App\Models\SocialFeedImageModel;
use App\Models\SocialFeedCommentModel;

class SocialFeedController extends BaseController
{
    protected $socialFeedModel;
    protected $userModel;
    protected $socialFeedImageModel;
    protected $socialFeedCommentModel;

    public function __construct()
    {
        $this->socialFeedModel = new SocialFeedModel();
        $this->userModel = new UserModel();
        $this->socialFeedImageModel = new SocialFeedImageModel();
        $this->socialFeedCommentModel = new SocialFeedCommentModel();
    }

    // Show social feed
    public function index()
    {
        $posts = $this->socialFeedModel->getPublicFeed();
        // Attach DB image URLs for each post
        foreach ($posts as &$post) {
            $imageRows = $this->socialFeedImageModel
                ->where('post_id', (int) ($post['post_id'] ?? 0))
                ->orderBy('sort_order', 'ASC')
                ->findAll();
            $post['image_urls'] = array_map(fn($row) => base_url('social-feed/image/' . $row['image_id']), $imageRows);
            $post['comments_count'] = $this->socialFeedCommentModel
                ->where('post_id', (int) ($post['post_id'] ?? 0))
                ->countAllResults();
        }
        unset($post);
        
        $data = [
            'title' => 'Social Feed',
            'posts' => $posts,
        ];

        return view('social_feed/index', $data);
    }

    // Show work showcase
    public function workShowcase()
    {
        $posts = $this->socialFeedModel->getWorkShowcase();
        foreach ($posts as &$post) {
            $imageRows = $this->socialFeedImageModel
                ->where('post_id', (int) ($post['post_id'] ?? 0))
                ->orderBy('sort_order', 'ASC')
                ->findAll();
            $post['image_urls'] = array_map(fn($row) => base_url('social-feed/image/' . $row['image_id']), $imageRows);
            $post['comments_count'] = $this->socialFeedCommentModel
                ->where('post_id', (int) ($post['post_id'] ?? 0))
                ->countAllResults();
        }
        unset($post);
        
        $data = [
            'title' => 'Work Showcase',
            'posts' => $posts,
        ];

        return view('social_feed/work_showcase', $data);
    }

    // Show status updates
    public function statusUpdates()
    {
        $posts = $this->socialFeedModel->getStatusUpdates();
        foreach ($posts as &$post) {
            $imageRows = $this->socialFeedImageModel
                ->where('post_id', (int) ($post['post_id'] ?? 0))
                ->orderBy('sort_order', 'ASC')
                ->findAll();
            $post['image_urls'] = array_map(fn($row) => base_url('social-feed/image/' . $row['image_id']), $imageRows);
            $post['comments_count'] = $this->socialFeedCommentModel
                ->where('post_id', (int) ($post['post_id'] ?? 0))
                ->countAllResults();
        }
        unset($post);
        
        $data = [
            'title' => 'Status Updates',
            'posts' => $posts,
        ];

        return view('social_feed/status_updates', $data);
    }

    // Create new post
    public function create()
    {
        $userId = session()->get('user_id');
        $userRole = session()->get('user_role');

        // Only barbers and owners can create posts
        if (!in_array($userRole, ['barber', 'owner'])) {
            return redirect()->to('/social-feed')->with('error', 'Unauthorized');
        }

        $data = [
            'title' => 'Create Post',
            'user_role' => $userRole,
        ];

        return view('social_feed/create', $data);
    }

    // Store new post
    public function store()
    {
        $userId = session()->get('user_id');
        $userRole = session()->get('user_role');

        // Any authenticated user can create posts (barber/owner/customers)
        if (empty($userId)) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $rules = [
            'post_type' => 'required|in_list[work_showcase,status_update,announcement]',
            'title' => 'required|min_length[3]|max_length[255]',
            'content' => 'required|min_length[3]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'error' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $data = $this->request->getPost();
        $data['user_id'] = $userId;
        $data['is_public'] = $this->request->getPost('is_public') ? 1 : 0;

        // Handle image uploads (store as BLOBs)
        $uploadedFiles = $this->request->getFileMultiple('images');
        $data['images'] = null; // legacy field no longer used

        // Handle status update
        if (($data['post_type'] ?? '') === 'status_update') {
            $data['status'] = $this->request->getPost('status') ?: 'open';
        }

        $postId = $this->socialFeedModel->insert($data);

        if ($postId) {
            // Persist image BLOBs with sort_order using query builder to ensure proper binding
            if ($uploadedFiles) {
                $order = 1;
                $builder = $this->socialFeedImageModel->builder();
                foreach ($uploadedFiles as $file) {
                    if ($file && $file->isValid() && !$file->hasMoved()) {
                        $tmp = $file->getTempName();
                        if (!is_readable($tmp)) {
                            continue;
                        }
                        $blob = file_get_contents($tmp);
                        $ok = $builder->insert([
                            'post_id' => (int) $postId,
                            'image' => $blob,
                            'sort_order' => $order++,
                        ]);
                        if ($ok === false) {
                            $dbError = $this->socialFeedImageModel->db->error();
                            return $this->response->setJSON([
                                'error' => 'Failed to save image',
                                'db_error' => $dbError,
                            ]);
                        }
                    }
                }
            }
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Post created successfully',
                'post_id' => $postId
            ]);
        } else {
            return $this->response->setJSON([
                'error' => 'Failed to create post'
            ]);
        }
    }

    // Show single post
    public function show($postId)
    {
        $post = $this->socialFeedModel->getPostWithDetails($postId);
        
        if (!$post) {
            return redirect()->to('/social-feed')->with('error', 'Post not found');
        }

        $data = [
            'title' => $post['title'],
            'post' => $post,
        ];

        return view('social_feed/show', $data);
    }

    // Like a post
    public function like($postId)
    {
        $result = $this->socialFeedModel->likePost($postId);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Post liked successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'error' => 'Failed to like post'
            ]);
        }
    }

    // Unlike a post
    public function unlike($postId)
    {
        $result = $this->socialFeedModel->unlikePost($postId);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Post unliked successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'error' => 'Failed to unlike post'
            ]);
        }
    }

    // Update barber status
    public function updateStatus()
    {
        $userId = session()->get('user_id');
        $userRole = session()->get('user_role');

        if ($userRole !== 'barber') {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $status = $this->request->getPost('status');
        $content = $this->request->getPost('content');

        if (!in_array($status, ['open', 'closed', 'on_break', 'busy'])) {
            return $this->response->setJSON(['error' => 'Invalid status']);
        }

        $postId = $this->socialFeedModel->updateBarberStatus($userId, $status, $content);

        if ($postId) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Status updated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'error' => 'Failed to update status'
            ]);
        }
    }

    // Get barber's current status
    public function getBarberStatus($barberId)
    {
        $status = $this->socialFeedModel->getBarberStatus($barberId);

        return $this->response->setJSON([
            'success' => true,
            'status' => $status
        ]);
    }

    // Search posts
    public function search()
    {
        $keyword = $this->request->getGet('keyword');

        if (!$keyword) {
            return redirect()->to('/social-feed');
        }

        $posts = $this->socialFeedModel->searchPosts($keyword);

        $data = [
            'title' => 'Search Results',
            'posts' => $posts,
            'keyword' => $keyword,
        ];

        return view('social_feed/search', $data);
    }

    // Get trending posts
    public function trending()
    {
        $posts = $this->socialFeedModel->getTrendingPosts();

        $data = [
            'title' => 'Trending Posts',
            'posts' => $posts,
        ];

        return view('social_feed/trending', $data);
    }

    // Get user's posts
    public function userPosts($userId)
    {
        $user = $this->userModel->find($userId);
        if (!$user) {
            return redirect()->to('/social-feed')->with('error', 'User not found');
        }

        $posts = $this->socialFeedModel->getUserPosts($userId);
        foreach ($posts as &$post) {
            $imageRows = $this->socialFeedImageModel
                ->where('post_id', (int) ($post['post_id'] ?? 0))
                ->orderBy('sort_order', 'ASC')
                ->findAll();
            $post['image_urls'] = array_map(fn($row) => base_url('social-feed/image/' . $row['image_id']), $imageRows);
            $post['comments_count'] = $this->socialFeedCommentModel
                ->where('post_id', (int) ($post['post_id'] ?? 0))
                ->countAllResults();
        }
        unset($post);

        $data = [
            'title' => $user['first_name'] . '\'s Posts',
            'posts' => $posts,
            'user' => $user,
        ];

        return view('social_feed/user_posts', $data);
    }

    // Delete post (only by post owner)
    public function delete($postId)
    {
        $userId = session()->get('user_id');
        $post = $this->socialFeedModel->find($postId);

        if (!$post) {
            return $this->response->setJSON(['error' => 'Post not found']);
        }

        if ($post['user_id'] != $userId) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $result = $this->socialFeedModel->delete($postId);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Post deleted successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'error' => 'Failed to delete post'
            ]);
        }
    }

    // Get barber portfolio
    public function barberPortfolio($barberId)
    {
        $barber = $this->userModel->find($barberId);
        if (!$barber || $barber['role'] !== 'barber') {
            return redirect()->to('/social-feed')->with('error', 'Barber not found');
        }

        $posts = $this->socialFeedModel->getUserPosts($barberId);
        $status = $this->socialFeedModel->getBarberStatus($barberId);

        $data = [
            'title' => $barber['first_name'] . '\'s Portfolio',
            'barber' => $barber,
            'posts' => $posts,
            'status' => $status,
        ];

        return view('social_feed/barber_portfolio', $data);
    }

    // Edit post (only by post owner)
    public function edit($postId)
    {
        $userId = session()->get('user_id');
        $post = $this->socialFeedModel->find($postId);

        if (!$post) {
            return redirect()->to('/social-feed')->with('error', 'Post not found');
        }

        if ($post['user_id'] != $userId) {
            return redirect()->to('/social-feed')->with('error', 'Unauthorized');
        }

        $data = [
            'title' => 'Edit Post',
            'post' => $post,
        ];

        return view('social_feed/edit', $data);
    }

    // Update post
    public function update($postId)
    {
        $userId = session()->get('user_id');
        $post = $this->socialFeedModel->find($postId);

        if (!$post) {
            return $this->response->setJSON(['error' => 'Post not found']);
        }

        if ($post['user_id'] != $userId) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $rules = [
            'post_type' => 'required|in_list[work_showcase,status_update,announcement]',
            'title' => 'required|min_length[3]|max_length[255]',
            'content' => 'required|min_length[10]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'error' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $data = [
            'post_type' => $this->request->getPost('post_type'),
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $result = $this->socialFeedModel->update($postId, $data);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Post updated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'error' => 'Failed to update post'
            ]);
        }
    }

    // Stream image stored as BLOB
    public function image($imageId)
    {
        $row = $this->socialFeedImageModel->find((int) $imageId);
        if (!$row || empty($row['image'])) {
            return $this->response->setStatusCode(404)->setBody('Not Found');
        }
        $blob = $row['image'];
        $info = @getimagesizefromstring($blob);
        $mime = is_array($info) && isset($info['mime']) ? $info['mime'] : 'image/jpeg';
        $this->response->setHeader('Content-Type', $mime);
        $this->response->setHeader('Cache-Control', 'public, max-age=31536000');
        return $this->response->setBody($blob);
    }

    // Fetch comments for a post
    public function comments($postId)
    {
        $comments = $this->socialFeedCommentModel->getCommentsForPost((int) $postId, 200);
        return $this->response->setJSON([
            'success' => true,
            'comments' => $comments,
        ]);
    }

    // Add a comment to a post
    public function addComment($postId)
    {
        $userId = session()->get('user_id');
        if (empty($userId)) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $content = trim((string) $this->request->getPost('content'));
        $parentId = $this->request->getPost('parent_comment_id');
        if ($content === '') {
            return $this->response->setJSON(['error' => 'Content is required']);
        }

        $ok = $this->socialFeedCommentModel->insert([
            'post_id' => (int) $postId,
            'user_id' => (int) $userId,
            'parent_comment_id' => $parentId ? (int) $parentId : null,
            'content' => $content,
        ]);

        if ($ok) {
            return $this->response->setJSON(['success' => true]);
        }

        $dbError = $this->socialFeedCommentModel->db->error();
        return $this->response->setJSON(['error' => 'Failed to add comment', 'db_error' => $dbError]);
    }
} 