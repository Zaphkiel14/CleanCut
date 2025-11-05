<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-user"></i> <?= esc(($user['first_name'] ?? 'User') . ' ' . ($user['last_name'] ?? '')) ?>
                </h1>
                <a href="<?= base_url('social-feed') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Feed
                </a>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12 d-flex align-items-center">
            <?php if (!empty($user['profile_picture'])): ?>
                <?php 
                    $pp = $user['profile_picture'];
                    $ppResolved = (is_file(FCPATH . $pp)) ? base_url($pp) : (is_file(FCPATH . 'writable/' . $pp) ? base_url('file/writable?path=' . rawurlencode($pp)) : base_url($pp));
                ?>
                <img src="<?= $ppResolved ?>" class="rounded-circle me-3" width="64" height="64" alt="Profile">
            <?php else: ?>
                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-3" style="width: 64px; height: 64px;">
                    <i class="fas fa-user text-white"></i>
                </div>
            <?php endif; ?>
            <div>
                <h5 class="mb-0"><?= esc(($user['first_name'] ?? 'User') . ' ' . ($user['last_name'] ?? '')) ?></h5>
                <small class="text-muted">Posts & activity</small>
            </div>
        </div>
    </div>

    <div class="row">
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <div class="col-12 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-transparent">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <?php if (!empty($user['profile_picture'])): ?>
                                        <img src="<?= $ppResolved ?>" class="rounded-circle" width="40" height="40" alt="Profile">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0"><?= esc(($user['first_name'] ?? 'User') . ' ' . ($user['last_name'] ?? '')) ?></h6>
                                    <small class="text-muted"><?= date('M d, Y', strtotime($post['created_at'])) ?></small>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <?php if (!empty($post['title'])): ?>
                                <h5 class="card-title"><?= esc($post['title']) ?></h5>
                            <?php endif; ?>
                            <?php if (!empty($post['content'])): ?>
                                <p class="card-text"><?= nl2br(esc($post['content'])) ?></p>
                            <?php endif; ?>

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
                                        <?php foreach ($blobUrls as $url): ?>
                                            <img src="<?= $url ?>" class="img-fluid rounded mb-2" alt="Post image">
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <?php foreach ($legacyImages as $image): ?>
                                            <?php 
                                                $imgResolved = (is_file(FCPATH . $image)) ? base_url($image) : (is_file(FCPATH . 'writable/' . $image) ? base_url('file/writable?path=' . rawurlencode($image)) : base_url($image));
                                            ?>
                                            <img src="<?= $imgResolved ?>" class="img-fluid rounded mb-2" alt="Post image">
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-footer bg-transparent d-flex align-items-center">
                            <button class="btn btn-sm btn-outline-primary me-2" onclick="likePost(<?= $post['post_id'] ?>)">
                                <i class="fas fa-heart"></i> <span id="likes-<?= $post['post_id'] ?>"><?= (int)($post['likes_count'] ?? 0) ?></span>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" type="button" onclick="toggleComments(<?= $post['post_id'] ?>)">
                                <i class="fas fa-comment"></i> <span id="comment-count-<?= $post['post_id'] ?>"><?= (int)($post['comments_count'] ?? 0) ?></span>
                            </button>
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
                    <h4>No posts yet</h4>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>








