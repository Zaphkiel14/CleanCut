<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-stream"></i> Social Feed
                </h1>
                <div class="d-flex gap-2">
                    <?php if (in_array(session()->get('user_role'), ['barber', 'owner'])): ?>
                        <a href="<?= base_url('social-feed/create') ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Post
                        </a>
                    <?php endif; ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= base_url('social-feed') ?>">All Posts</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('social-feed/work-showcase') ?>">Work Showcase</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('social-feed/status-updates') ?>">Status Updates</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('social-feed/trending') ?>">Trending</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="<?= base_url('social-feed/search') ?>" method="GET" class="row g-3">
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="keyword" placeholder="Search posts, barbers, or styles..." 
                                   value="<?= $keyword ?? '' ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Posts Grid -->
    <div class="row">
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <div class="col-12 mb-4">
                    <div class="card h-100 post-card">
                        <!-- Post Header -->
                        <div class="card-header bg-transparent">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <?php if (!empty($post['profile_picture'])): ?>
                                        <?php 
                                            $pp = $post['profile_picture'];
                                            $ppResolved = (is_file(FCPATH . $pp)) ? base_url($pp) : (is_file(FCPATH . 'writable/' . $pp) ? base_url('file/writable?path=' . rawurlencode($pp)) : base_url($pp));
                                        ?>
                                        <img src="<?= $ppResolved ?>" 
                                             class="rounded-circle" width="40" height="40" alt="Profile">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px;">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0"><?= $post['first_name'] . ' ' . $post['last_name'] ?></h6>
                                    <small class="text-muted">
                                        <?= date('M d, Y', strtotime($post['created_at'])) ?>
                                        <?php if ($post['post_type'] === 'work_showcase'): ?>
                                            <span class="badge bg-primary ms-2">Work Showcase</span>
                                        <?php elseif ($post['post_type'] === 'status_update'): ?>
                                            <span class="badge bg-info ms-2">Status Update</span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="<?= base_url('social-feed/post/' . $post['post_id']) ?>">
                                                <i class="fas fa-eye"></i> View Details
                                            </a></li>
                                            <?php if (session()->get('user_id') == $post['user_id']): ?>
                                                <li><a class="dropdown-item" href="<?= base_url('social-feed/edit/' . $post['post_id']) ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="deletePost(<?= $post['post_id'] ?>)">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Post Content -->
                        <div class="card-body">
                            <?php if ($post['title']): ?>
                                <h5 class="card-title"><?= $post['title'] ?></h5>
                            <?php endif; ?>
                            
                            <?php if ($post['content']): ?>
                                <p class="card-text"><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                            <?php endif; ?>

                            <!-- Images Gallery (DB blobs preferred) -->
                            <?php 
                                $blobUrls = $post['image_urls'] ?? [];
                                $legacyImages = [];
                                if (empty($blobUrls) && !empty($post['images'])) {
                                    $legacyImages = json_decode($post['images'], true) ?: [];
                                }
                            ?>
                            <?php if (!empty($blobUrls) || !empty($legacyImages)): ?>
                                <div class="post-images mb-3">
                                    <?php if (!empty($blobUrls)): ?>
                                        <?php if (count($blobUrls) === 1): ?>
                                            <img src="<?= $blobUrls[0] ?>" class="img-fluid rounded" alt="Post image">
                                        <?php else: ?>
                                            <div class="row g-2">
                                                <?php foreach (array_slice($blobUrls, 0, 4) as $url): ?>
                                                    <div class="col-6">
                                                        <img src="<?= $url ?>" class="img-fluid rounded" alt="Post image">
                                                    </div>
                                                <?php endforeach; ?>
                                                <?php if (count($blobUrls) > 4): ?>
                                                    <div class="col-6">
                                                        <div class="position-relative">
                                                            <img src="<?= $blobUrls[3] ?>" class="img-fluid rounded" alt="Post image">
                                                            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex align-items-center justify-content-center rounded">
                                                                <span class="text-white fw-bold">+<?= count($blobUrls) - 4 ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php if (count($legacyImages) === 1): ?>
                                            <?php 
                                                $img0 = $legacyImages[0];
                                                $img0Resolved = (is_file(FCPATH . $img0)) ? base_url($img0) : (is_file(FCPATH . 'writable/' . $img0) ? base_url('file/writable?path=' . rawurlencode($img0)) : base_url($img0));
                                            ?>
                                            <img src="<?= $img0Resolved ?>" class="img-fluid rounded" alt="Post image">
                                        <?php else: ?>
                                            <div class="row g-2">
                                                <?php foreach (array_slice($legacyImages, 0, 4) as $image): ?>
                                                    <div class="col-6">
                                                        <?php 
                                                            $imgResolved = (is_file(FCPATH . $image)) ? base_url($image) : (is_file(FCPATH . 'writable/' . $image) ? base_url('file/writable?path=' . rawurlencode($image)) : base_url($image));
                                                        ?>
                                                        <img src="<?= $imgResolved ?>" class="img-fluid rounded" alt="Post image">
                                                    </div>
                                                <?php endforeach; ?>
                                                <?php if (count($legacyImages) > 4): ?>
                                                    <div class="col-6">
                                                        <div class="position-relative">
                                                            <?php 
                                                                $img3 = $legacyImages[3];
                                                                $img3Resolved = (is_file(FCPATH . $img3)) ? base_url($img3) : (is_file(FCPATH . 'writable/' . $img3) ? base_url('file/writable?path=' . rawurlencode($img3)) : base_url($img3));
                                                            ?>
                                                            <img src="<?= $img3Resolved ?>" class="img-fluid rounded" alt="Post image">
                                                            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex align-items-center justify-content-center rounded">
                                                                <span class="text-white fw-bold">+<?= count($legacyImages) - 4 ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Status Badge for Barbers -->
                            <?php if ($post['status'] && $post['post_type'] === 'status_update'): ?>
                                <div class="mb-3">
                                    <span class="status-badge status-<?= $post['status'] ?>">
                                        <?= ucfirst(str_replace('_', ' ', $post['status'])) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Post Footer -->
                        <div class="card-footer bg-transparent">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <button class="btn btn-sm btn-outline-primary me-2" onclick="likePost(<?= $post['post_id'] ?>)">
                                        <i class="fas fa-heart"></i> 
                                        <span id="likes-<?= $post['post_id'] ?>"><?= $post['likes_count'] ?></span>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-post-id="<?= $post['post_id'] ?>" onclick="toggleComments(<?= $post['post_id'] ?>)">
                                        <i class="fas fa-comment"></i> <span id="comment-count-<?= $post['post_id'] ?>"><?= (int)($post['comments_count'] ?? 0) ?></span>
                                    </button>
                                </div>
                                <div>
                                    <a href="<?= base_url('social-feed/user/' . $post['user_id']) ?>" class="text-muted text-decoration-none">
                                        <small>View Profile</small>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="border-top p-3 d-none" id="comments-section-<?= $post['post_id'] ?>">
                            <div id="comments-list-<?= $post['post_id'] ?>" class="mb-3"></div>
                            <form class="d-flex gap-2" onsubmit="return submitComment(<?= $post['post_id'] ?>, this)">
                                <input type="text" name="content" class="form-control" placeholder="Write a comment..." required>
                                <button class="btn btn-primary" type="submit">Post</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center text-muted py-5">
                    <i class="fas fa-stream fa-3x mb-3"></i>
                    <h4>No posts found</h4>
                    <p>Be the first to share your work or check back later for new posts.</p>
                    <?php if (in_array(session()->get('user_role'), ['barber', 'owner'])): ?>
                        <a href="<?= base_url('social-feed/create') ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Your First Post
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Load More Button -->
    <?php if (!empty($posts) && count($posts) >= 12): ?>
        <div class="row">
            <div class="col-12 text-center">
                <button class="btn btn-outline-primary" onclick="loadMorePosts()">
                    <i class="fas fa-plus"></i> Load More Posts
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let currentPage = 1;

function likePost(postId) {
    $.post('<?= base_url('social-feed/post') ?>/' + postId + '/like', function(response) {
        if (response.success) {
            const likesElement = $('#likes-' + postId);
            const currentLikes = parseInt(likesElement.text());
            likesElement.text(currentLikes + 1);
            const button = likesElement.closest('button');
            button.removeClass('btn-outline-primary').addClass('btn-primary');
            button.attr('onclick', 'unlikePost(' + postId + ')');
        } else {
            alert('Error: ' + response.error);
        }
    });
}
function unlikePost(postId) {
    $.post('<?= base_url('social-feed/post') ?>/' + postId + '/unlike', function(response) {
        if (response.success) {
            const likesElement = $('#likes-' + postId);
            const currentLikes = Math.max(0, parseInt(likesElement.text()) - 1);
            likesElement.text(currentLikes);
            const button = likesElement.closest('button');
            button.removeClass('btn-primary').addClass('btn-outline-primary');
            button.attr('onclick', 'likePost(' + postId + ')');
        } else {
            alert('Error: ' + response.error);
        }
    });
}

function deletePost(postId) {
    if (confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
        $.ajax({
            url: '<?= base_url('social-feed/delete') ?>/' + postId,
            type: 'DELETE',
            success: function(response) {
                if (response.success) {
                    // Remove the post card from the DOM
                    $('.post-card').each(function() {
                        if ($(this).find('[onclick*="deletePost(' + postId + ')"]').length > 0) {
                            $(this).closest('.col-lg-6').remove();
                        }
                    });
                    
                    // Show success message
                    alert('Post deleted successfully');
                } else {
                    alert('Error: ' + response.error);
                }
            },
            error: function() {
                alert('Error deleting post');
            }
        });
    }
}

function loadMorePosts() {
    currentPage++;
    $.get('<?= base_url('social-feed') ?>', { page: currentPage }, function(data) {
        if (data.success && data.posts.length > 0) {
            // Append new posts to the grid
            data.posts.forEach(post => {
                // Create post HTML and append to grid
                // This would require a more complex implementation
            });
        } else {
            // Hide load more button if no more posts
            $('.btn-outline-primary').hide();
        }
    });
}

// Auto-refresh posts every 5 minutes
setInterval(function() {
    // Optionally refresh posts to show new content
    // This could be implemented with WebSocket for real-time updates
}, 300000);

function toggleComments(postId) {
    const section = document.getElementById('comments-section-' + postId);
    if (!section) return;
    const isHidden = section.classList.contains('d-none');
    if (isHidden) {
        // Show and load
        section.classList.remove('d-none');
        loadComments(postId);
    } else {
        section.classList.add('d-none');
    }
}

function loadComments(postId) {
    const listEl = document.getElementById('comments-list-' + postId);
    if (!listEl) return;
    listEl.innerHTML = '<div class="text-muted">Loading comments...</div>';
    fetch('<?= base_url('social-feed/post') ?>/' + postId + '/comments', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                listEl.innerHTML = '<div class="text-danger">Failed to load comments</div>';
                return;
            }
            if (!data.comments || data.comments.length === 0) {
                listEl.innerHTML = '<div class="text-muted">No comments yet.</div>';
                return;
            }
            listEl.innerHTML = data.comments.map(c => renderComment(c)).join('');
        })
        .catch(() => {
            listEl.innerHTML = '<div class="text-danger">Failed to load comments</div>';
        });
}

function renderComment(c) {
    const name = (c.first_name ? c.first_name : 'User') + (c.last_name ? ' ' + c.last_name : '');
    return `
    <div class="d-flex mb-2">
        <div class="flex-shrink-0 me-2">
            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                <i class="fas fa-user text-white" style="font-size: 0.75rem;"></i>
            </div>
        </div>
        <div class="flex-grow-1">
            <div class="bg-light rounded p-2">
                <strong>${name}</strong>
                <div>${escapeHtml(c.content || '')}</div>
            </div>
            <small class="text-muted">${c.created_at ? new Date(c.created_at).toLocaleString() : ''}</small>
        </div>
    </div>`;
}

function submitComment(postId, formEl) {
    const input = formEl.querySelector('input[name="content"]');
    if (!input || !input.value.trim()) return false;
    const formData = new FormData();
    formData.append('content', input.value.trim());
    formData.append('csrf_test_name', document.querySelector('input[name="csrf_test_name"]')?.value || '');
    fetch('<?= base_url('social-feed/post') ?>/' + postId + '/comment', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            loadComments(postId);
            // increment visible comment count
            const cc = document.getElementById('comment-count-' + postId);
            if (cc) cc.textContent = String(parseInt(cc.textContent || '0') + 1);
        } else {
            alert(data.error || 'Failed to add comment');
        }
    })
    .catch(() => alert('Failed to add comment'));
    return false;
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>
<?= $this->endSection() ?> 