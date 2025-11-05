<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-edit"></i> Edit Post
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success">
                            <?= session()->getFlashdata('success') ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('social-feed/update/' . $post['post_id']) ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label for="post_type" class="form-label">Post Type</label>
                            <select class="form-select" id="post_type" name="post_type" required>
                                <option value="work_showcase" <?= ($post['post_type'] == 'work_showcase') ? 'selected' : '' ?>>Work Showcase</option>
                                <option value="status_update" <?= ($post['post_type'] == 'status_update') ? 'selected' : '' ?>>Status Update</option>
                                <option value="announcement" <?= ($post['post_type'] == 'announcement') ? 'selected' : '' ?>>Announcement</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?= esc($post['title']) ?>" required minlength="3" maxlength="255">
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control" id="content" name="content" rows="6" required minlength="10"><?= esc($post['content']) ?></textarea>
                        </div>

                        <?php if ($post['post_type'] == 'work_showcase' && !empty($post['image_url'])): ?>
                        <div class="mb-3">
                            <label class="form-label">Current Image</label>
                            <div>
                                <img src="<?= base_url('uploads/' . $post['image_url']) ?>" alt="Current post image" class="img-thumbnail" style="max-width: 200px;">
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="image" class="form-label">New Image (optional)</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="form-text">Leave empty to keep the current image</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= base_url('social-feed') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Feed
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Post
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const postTypeSelect = document.getElementById('post_type');
    const imageField = document.getElementById('image');
    const imageLabel = imageField.parentElement.querySelector('.form-label');
    const imageHelp = imageField.parentElement.querySelector('.form-text');

    function toggleImageField() {
        if (postTypeSelect.value === 'work_showcase') {
            imageField.style.display = 'block';
            imageLabel.style.display = 'block';
            imageHelp.style.display = 'block';
        } else {
            imageField.style.display = 'none';
            imageLabel.style.display = 'none';
            imageHelp.style.display = 'none';
        }
    }

    postTypeSelect.addEventListener('change', toggleImageField);
    toggleImageField(); // Initial state
});
</script>
<?= $this->endSection() ?>
