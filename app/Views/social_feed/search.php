<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-search"></i> Search Results
                </h1>
                <a href="<?= base_url('social-feed') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Feed
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Search Results for: "<?= esc($keyword) ?>"</h5>
                    <p class="text-muted">Found <?= count($posts) ?> result(s)</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <?php if (empty($posts)): ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4>No results found</h4>
                        <p class="text-muted">Try searching with different keywords or browse our feed.</p>
                        <a href="<?= base_url('social-feed') ?>" class="btn btn-primary">Browse Feed</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <?php if (!empty($post['images'])): ?>
                            <div class="card-img-top" style="height: 200px; background-image: url('<?= base_url('uploads/' . json_decode($post['images'])[0]) ?>'); background-size: cover; background-position: center;"></div>
                        <?php endif; ?>
                        
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title mb-0"><?= esc($post['title']) ?></h6>
                                <span class="badge bg-<?= $post['post_type'] === 'work_showcase' ? 'primary' : ($post['post_type'] === 'status_update' ? 'success' : 'info') ?>">
                                    <?= ucfirst(str_replace('_', ' ', $post['post_type'])) ?>
                                </span>
                            </div>
                            
                            <p class="card-text text-muted small flex-grow-1">
                                <?= esc(substr($post['content'], 0, 100)) ?><?= strlen($post['content']) > 100 ? '...' : '' ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <small class="text-muted">
                                    <i class="fas fa-user"></i> <?= esc($post['first_name'] . ' ' . $post['last_name']) ?>
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-heart"></i> <?= $post['likes_count'] ?>
                                </small>
                            </div>
                            
                            <div class="mt-2">
                                <a href="<?= base_url('social-feed/show/' . $post['post_id']) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View Post
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
