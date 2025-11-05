<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-user-circle"></i> <?= $user['role'] === 'owner' ? 'Shop Settings' : 'Profile Settings' ?>
                </h1>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-edit"></i> Personal Information
                    </h5>
                </div>
                <div class="card-body">
                    <form id="profile-form">
                        <input type="hidden" name="csrf_test_name" value="<?= csrf_hash() ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                    value="<?= esc($user['first_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                                    value="<?= esc($user['last_name'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?= esc($user['email'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                    value="<?= esc($user['phone'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Role</label>
                                <input type="text" class="form-control" id="role" value="<?= ucfirst(esc($user['role'] ?? '')) ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="created_at" class="form-label">Member Since</label>
                                <input type="text" class="form-control" id="created_at"
                                    value="<?= date('M d, Y', strtotime($user['created_at'] ?? '')) ?>" readonly>
                            </div>
                        </div>
                        <?php if ($user['role'] === 'owner'): ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="shop_name" class="form-label">Shop Name</label>
                                    <input type="text" class="form-control" id="shop_name" name="shop_name" value="<?= esc($shop['shop_name'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="shop_address" class="form-label">Shop Address</label>
                                    <input type="text" class="form-control" id="shop_address" name="shop_address" value="<?= esc($shop['address'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="booking_fee_percentage" class="form-label">Booking Fee Percentage</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" max="100" class="form-control" id="booking_fee_percentage" name="booking_fee_percentage" value="<?= esc($shop['booking_fee_percentage'] ?? '') ?>" placeholder="e.g. 10.00">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <small class="text-muted">Applied to bookings with barbers affiliated to your shop.</small>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($user['role'] === 'barber' && ($is_freelance_barber ?? false)): ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="freelance_booking_fee_percentage" class="form-label">Booking Fee Percentage</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" max="100" class="form-control" id="freelance_booking_fee_percentage" name="freelance_booking_fee_percentage" value="<?= esc($user['freelance_booking_fee_percentage'] ?? '') ?>" placeholder="e.g. 10.00">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <small class="text-muted">Used when you are booked as a freelance barber.</small>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= $user['role'] === 'owner' ? 'Update Shop Information' : 'Update Profile' ?>
                            </button>
                            <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </form>
                    <div id="profile-message" class="mt-3"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-camera"></i> Profile Photo
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php if (!empty($user['profile_photo'])): ?>
                            <img src="<?= base_url($user['profile_photo']) ?>"
                                alt="Profile Photo" class="rounded-circle"
                                style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center"
                                style="width: 150px; height: 150px;">
                                <i class="fas fa-user fa-3x text-white"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <form id="photo-form" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_test_name" value="<?= csrf_hash() ?>">
                        <div class="mb-3">
                            <label for="profile_photo" class="form-label">Upload New Photo</label>
                            <input type="file" class="form-control" id="profile_photo" name="profile_photo"
                                accept="image/*">
                            <div class="form-text">Maximum file size: 5MB. Supported formats: JPG, PNG, GIF</div>
                        </div>
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-upload"></i> Upload Photo
                        </button>
                    </form>
                    <div id="photo-message" class="mt-3"></div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-shield-alt"></i> Account Security
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Keep your account secure by regularly updating your password.</p>
                    <a href="#" class="btn btn-outline-warning" onclick="alert('Password change feature coming soon!')">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Social Post Composer -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-feather-alt"></i> Create a Post
                </h5>
            </div>
            <div class="card-body">
                <form id="post-form" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_test_name" value="<?= csrf_hash() ?>">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="What's on your mind?" required>
                            <div class="form-text">Keep it concise and descriptive.</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="post_type" class="form-label">Post Type *</label>
                            <select class="form-control" id="post_type" name="post_type" required>
                                <option value="work_showcase">Work Showcase</option>
                                <option value="status_update">Status Update</option>
                                <option value="announcement">Announcement</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="content" class="form-label">Content *</label>
                        <textarea class="form-control" id="content" name="content" rows="4" placeholder="Share an update..." required></textarea>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Share details, promotions, or showcase your latest work.</small>
                            <small id="content-counter" class="text-muted">0 characters</small>
                        </div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-md-8 mb-3 mb-md-0">
                            <label for="images" class="form-label">Images</label>
                            <div id="images-dropzone" class="dropzone">
                                <i class="fas fa-images me-2"></i>
                                Drag & drop images here or click to select
                            </div>
                            <input type="file" class="form-control d-none" id="images" name="images[]" accept="image/*" multiple>
                            <div class="form-text">Upload up to a few images to make your post stand out.</div>
                            <div id="images-preview" class="image-preview-grid mt-2"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="is_public" name="is_public" checked>
                                <label class="form-check-label" for="is_public">Public post (visible to everyone)</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Post
                        </button>
                        <button type="reset" class="btn btn-outline-secondary">
                            <i class="fas fa-eraser"></i> Clear
                        </button>
                    </div>
                </form>
                <div id="post-message" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Profile form submission
        document.getElementById('profile-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const messageDiv = document.getElementById('profile-message');

            // Add CSRF token to form data
            const csrfToken = document.querySelector('input[name="csrf_test_name"]').value;
            formData.append('csrf_test_name', csrfToken);

            // Show loading state
            messageDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Updating profile...</div>';

            // Determine if this is a shop owner and include shop data
            const userRole = '<?= $user['role'] ?? '' ?>';
            let updateUrl = '<?= base_url('profile/update') ?>';

            if (userRole === 'owner') {
                updateUrl = '<?= base_url('profile/update-shop') ?>';
            }

            fetch(updateUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text().then(text => {
                        console.log('Response text:', text);
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Failed to parse JSON:', e);
                            console.error('Response was:', text);
                            throw new Error('Invalid JSON response');
                        }
                    });
                })
                .then(data => {
                    console.log('Parsed data:', data);
                    if (data.success) {
                        messageDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' + (data.message || 'Profile updated successfully') + '</div>';

                        // Update the navigation display name if shop name was updated
                        if (userRole === 'owner' && data.shop_name) {
                            // Reload the page to update the navigation
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        } else {
                            setTimeout(() => {
                                messageDiv.innerHTML = '';
                            }, 3000);
                        }
                    } else {
                        messageDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' + (data.error || 'Failed to update profile') + '</div>';
                    }
                })
                .catch(error => {
                    messageDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> An error occurred. Please try again.</div>';
                    console.error('Error:', error);
                    console.error('Error details:', {
                        message: error.message,
                        stack: error.stack
                    });
                });
        });

        // Photo upload form submission
        document.getElementById('photo-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const messageDiv = document.getElementById('photo-message');
            const fileInput = document.getElementById('profile_photo');
            if (!fileInput || fileInput.files.length === 0) {
                messageDiv.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Please select an image file to upload.</div>';
                return;
            }

            // Get fresh CSRF token
            const csrfToken = document.querySelector('input[name="csrf_test_name"]').value;
            formData.append('csrf_test_name', csrfToken);

            // Show loading state
            messageDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Uploading photo...</div>';

            fetch('<?= base_url('profile/upload-photo') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: formData
                })
                .then(async response => {
                    let data;
                    try {
                        data = await response.json();
                    } catch (e) {
                        const text = await response.text();
                        throw new Error(text || `HTTP error ${response.status}`);
                    }
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        messageDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' + (data.message || 'Photo uploaded successfully') + '</div>';

                        // Update the profile photo display
                        const photoContainer = document.querySelector('.card-body .mb-3');
                        if (data.photo_path) {
                            photoContainer.innerHTML = `<img src="${data.photo_path}" alt="Profile Photo" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">`;
                        }

                        // Clear the file input
                        document.getElementById('profile_photo').value = '';

                        setTimeout(() => {
                            messageDiv.innerHTML = '';
                        }, 3000);
                    } else {
                        messageDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' + (data.error || 'Failed to upload photo') + '</div>';
                    }
                })
                .catch(error => {
                    messageDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> An error occurred. Please try again.</div>';
                    console.error('Error:', error);
                });
        });

        // Social post form submission
        const postForm = document.getElementById('post-form');
        if (postForm) {
            postForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const messageDiv = document.getElementById('post-message');

                // Ensure CSRF token is present
                const csrfToken = document.querySelector('input[name="csrf_test_name"]').value;
                formData.set('csrf_test_name', csrfToken);
                // If status update, include a default status if none provided
                const typeSelect = document.getElementById('post_type');
                if (typeSelect && typeSelect.value === 'status_update' && !formData.has('status')) {
                    formData.append('status', 'open');
                }

                messageDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Posting...</div>';

                // Read CSRF cookie for header as an extra safeguard
                const getCookie = (name) => document.cookie.split('; ').find(row => row.startsWith(name + '='))?.split('=')[1];
                const csrfCookie = getCookie('csrf_cookie_name');
                fetch('<?= base_url('social-feed/store') ?>', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            ...(csrfCookie ? {
                                'X-CSRF-TOKEN': decodeURIComponent(csrfCookie)
                            } : {})
                        },
                        credentials: 'same-origin',
                        body: formData
                    })
                    .then(async response => {
                        const text = await response.text();
                        let data;
                        try {
                            data = JSON.parse(text);
                        } catch (e) {
                            data = null;
                        }
                        if (!response.ok) {
                            throw new Error(data?.error || text || `HTTP ${response.status}`);
                        }
                        return data || {
                            success: true,
                            message: text
                        };
                    })
                    .then(data => {
                        if (data.success) {
                            messageDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' + (data.message || 'Posted!') + '</div>';
                            postForm.reset();
                            setTimeout(() => {
                                messageDiv.innerHTML = '';
                            }, 3000);
                        } else {
                            const err = data.error || (data.errors ? Object.values(data.errors).join('<br>') : 'Failed to post');
                            messageDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' + err + '</div>';
                        }
                    })
                    .catch(error => {
                        messageDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' + (error?.message || 'An error occurred') + '</div>';
                        console.error('Error posting:', error);
                    });
            });
            // Content character counter
            const contentInput = document.getElementById('content');
            const counter = document.getElementById('content-counter');
            if (contentInput && counter) {
                const updateCount = () => {
                    counter.textContent = `${contentInput.value.length} characters`;
                };
                contentInput.addEventListener('input', updateCount);
                updateCount();
            }
            // Image dropzone and preview
            const dropzone = document.getElementById('images-dropzone');
            const imagesInput = document.getElementById('images');
            const previewGrid = document.getElementById('images-preview');
            if (dropzone && imagesInput && previewGrid) {
                const openPicker = () => imagesInput.click();
                dropzone.addEventListener('click', openPicker);
                ['dragenter', 'dragover'].forEach(evt => dropzone.addEventListener(evt, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('dragover');
                }));;
                ['dragleave', 'drop'].forEach(evt => dropzone.addEventListener(evt, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('dragover');
                }));
                dropzone.addEventListener('drop', (e) => {
                    const files = e.dataTransfer.files;
                    imagesInput.files = files;
                    renderPreviews(files);
                });
                imagesInput.addEventListener('change', (e) => {
                    renderPreviews(e.target.files);
                });

                function renderPreviews(fileList) {
                    previewGrid.innerHTML = '';
                    Array.from(fileList).forEach((file) => {
                        if (!file.type.startsWith('image/')) return;
                        const reader = new FileReader();
                        reader.onload = (ev) => {
                            const item = document.createElement('div');
                            item.className = 'image-preview-item';
                            item.innerHTML = `<img src="${ev.target.result}" alt="preview">`;
                            previewGrid.appendChild(item);
                        };
                        reader.readAsDataURL(file);
                    });
                }
            }
            // Reset clears previews
            postForm.addEventListener('reset', () => {
                const preview = document.getElementById('images-preview');
                if (preview) preview.innerHTML = '';
                const messageDiv = document.getElementById('post-message');
                if (messageDiv) messageDiv.innerHTML = '';
            });
        }
    });
</script>

<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .btn {
        border-radius: 0.375rem;
    }

    .alert {
        border-radius: 0.375rem;
        margin-bottom: 0;
    }

    .rounded-circle {
        border: 3px solid #dee2e6;
    }

    /* Social composer enhancements */
    .dropzone {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        border: 2px dashed #ced4da;
        border-radius: 0.5rem;
        color: #6c757d;
        cursor: pointer;
        transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
        background-color: #fafafa;
    }

    .dropzone.dragover {
        background-color: #f1f8ff;
        border-color: #86b7fe;
        color: #0d6efd;
    }

    .image-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
        gap: 0.5rem;
    }

    .image-preview-item {
        width: 100%;
        aspect-ratio: 1 / 1;
        overflow: hidden;
        border-radius: 0.5rem;
        border: 1px solid #e9ecef;
        background: #fff;
    }

    .image-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
</style>

<?= $this->endSection() ?>