<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-plus-circle"></i> Create New Post
                    </h4>
                </div>
                <div class="card-body">
                    <form id="create-post-form" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Post Type Selection -->
                                <div class="form-group mb-3">
                                    <label for="post_type" class="form-label">Post Type *</label>
                                    <select class="form-select" id="post_type" name="post_type" required>
                                        <option value="">Select post type</option>
                                        <option value="work_showcase">Work Showcase</option>
                                        <option value="status_update">Status Update</option>
                                        <option value="announcement">Announcement</option>
                                    </select>
                                </div>

                                <!-- Title -->
                                <div class="form-group mb-3">
                                    <label for="title" class="form-label">Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           placeholder="Enter post title" required minlength="3" maxlength="255">
                                </div>

                                <!-- Content -->
                                <div class="form-group mb-3">
                                    <label for="content" class="form-label">Content *</label>
                                    <textarea class="form-control" id="content" name="content" rows="6" 
                                              placeholder="Write your post content here..." required minlength="10"></textarea>
                                </div>

                                <!-- Status Update Fields (initially hidden) -->
                                <div id="status-fields" class="form-group mb-3" style="display: none;">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="available">Available</option>
                                        <option value="busy">Busy</option>
                                        <option value="offline">Offline</option>
                                        <option value="custom">Custom</option>
                                    </select>
                                </div>

                                <!-- Images Upload -->
                                <div class="form-group mb-3">
                                    <label for="images" class="form-label">Images (optional)</label>
                                    <input type="file" class="form-control" id="images" name="images[]" 
                                           multiple accept="image/*">
                                    <small class="form-text text-muted">You can select multiple images. Max 5 images.</small>
                                </div>

                                <!-- Privacy Setting -->
                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_public" name="is_public" checked>
                                        <label class="form-check-label" for="is_public">
                                            Make this post public
                                        </label>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Create Post
                                    </button>
                                    <a href="<?= base_url('social-feed') ?>" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Preview Section -->
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-eye"></i> Preview
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="post-preview">
                                            <p class="text-muted">Fill in the form to see a preview of your post.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tips Section -->
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-lightbulb"></i> Tips
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle"></i> 
                                                    <strong>Work Showcase:</strong> Share your best haircuts and styles
                                                </small>
                                            </li>
                                            <li class="mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle"></i> 
                                                    <strong>Status Update:</strong> Let customers know your availability
                                                </small>
                                            </li>
                                            <li class="mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle"></i> 
                                                    <strong>Announcement:</strong> Share important news or updates
                                                </small>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle text-success"></i> Success
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Your post has been created successfully!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="<?= base_url('social-feed') ?>" class="btn btn-primary">View All Posts</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('create-post-form');
    const postTypeSelect = document.getElementById('post_type');
    const statusFields = document.getElementById('status-fields');
    const titleInput = document.getElementById('title');
    const contentInput = document.getElementById('content');
    const previewDiv = document.getElementById('post-preview');

    // Show/hide status fields based on post type
    postTypeSelect.addEventListener('change', function() {
        if (this.value === 'status_update') {
            statusFields.style.display = 'block';
        } else {
            statusFields.style.display = 'none';
        }
        updatePreview();
    });

    // Update preview as user types
    titleInput.addEventListener('input', updatePreview);
    contentInput.addEventListener('input', updatePreview);

    function updatePreview() {
        const title = titleInput.value || 'Your Post Title';
        const content = contentInput.value || 'Your post content will appear here...';
        const postType = postTypeSelect.value;

        let typeLabel = '';
        switch(postType) {
            case 'work_showcase':
                typeLabel = '<span class="badge bg-primary">Work Showcase</span>';
                break;
            case 'status_update':
                typeLabel = '<span class="badge bg-info">Status Update</span>';
                break;
            case 'announcement':
                typeLabel = '<span class="badge bg-warning">Announcement</span>';
                break;
            default:
                typeLabel = '<span class="badge bg-secondary">Post</span>';
        }

        previewDiv.innerHTML = `
            <div class="border rounded p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="mb-0">${title}</h6>
                    ${typeLabel}
                </div>
                <p class="mb-2 text-muted small">${content}</p>
                <div class="text-muted small">
                    <i class="fas fa-clock"></i> Just now
                </div>
            </div>
        `;
    }

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        
        // Add CSRF token
        const csrfToken = document.querySelector('input[name="csrf_test_name"]').value;
        formData.append('csrf_test_name', csrfToken);

        fetch('<?= base_url('social-feed/post') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success modal
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
                
                // Reset form
                form.reset();
                updatePreview();
            } else {
                // Show error message
                alert('Error: ' + (data.error || 'Failed to create post'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while creating the post. Please try again.');
        });
    });

    // Initialize preview
    updatePreview();
});
</script>

<?= $this->endSection() ?>
