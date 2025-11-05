<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mt-4">Rate Your Haircut</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/history">History</a></li>
                <li class="breadcrumb-item active">Rate Haircut</li>
            </ol>
        </div>
        <div>
            <a href="/history/view/<?= $history['history_id'] ?>" class="btn btn-info">
                <i class="fas fa-eye"></i> View Details
            </a>
            <a href="/history" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to History
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-star"></i> Rate Your Experience
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Haircut Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Haircut Details</h6>
                            <p><strong>Style:</strong> <?= esc($history['style_name']) ?></p>
                            <p><strong>Date:</strong> <?= date('M d, Y', strtotime($history['haircut_date'])) ?></p>
                            <p><strong>Cost:</strong> ₱<?= number_format($history['total_cost'], 2) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Barber Information</h6>
                            <p><strong>Barber:</strong> <?= esc($barber['first_name'] . ' ' . $barber['last_name']) ?></p>
                            <p><strong>Shop:</strong> <?= esc($barber['shop_name'] ?? 'N/A') ?></p>
                        </div>
                    </div>

                    <form action="<?= base_url('history/submit-rating/' . $history['history_id']) ?>" method="post">
                        <?= csrf_field() ?>
                        
                        <!-- Rating -->
                        <div class="mb-4">
                            <label for="customer_rating" class="form-label">How would you rate your haircut? *</label>
                            <div class="rating-stars mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <input type="radio" id="star<?= $i ?>" name="customer_rating" value="<?= $i ?>" 
                                           class="rating-input" <?= old('customer_rating', $history['customer_rating']) == $i ? 'checked' : '' ?>>
                                    <label for="star<?= $i ?>" class="rating-star">
                                        <i class="fas fa-star"></i>
                                    </label>
                                <?php endfor; ?>
                            </div>
                            <div class="rating-labels">
                                <span class="text-muted">1 - Poor</span>
                                <span class="text-muted">2 - Fair</span>
                                <span class="text-muted">3 - Good</span>
                                <span class="text-muted">4 - Very Good</span>
                                <span class="text-muted">5 - Excellent</span>
                            </div>
                            <?php if (session()->getFlashdata('errors.customer_rating')): ?>
                                <div class="text-danger mt-1">
                                    <?= session()->getFlashdata('errors.customer_rating') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Feedback -->
                        <div class="mb-4">
                            <label for="customer_feedback" class="form-label">Share your feedback (optional)</label>
                            <textarea class="form-control <?= session()->getFlashdata('errors.customer_feedback') ? 'is-invalid' : '' ?>" 
                                      id="customer_feedback" name="customer_feedback" rows="4" 
                                      placeholder="Tell us about your experience, what you liked, or any suggestions for improvement..."><?= old('customer_feedback', $history['customer_feedback']) ?></textarea>
                            <?php if (session()->getFlashdata('errors.customer_feedback')): ?>
                                <div class="invalid-feedback">
                                    <?= session()->getFlashdata('errors.customer_feedback') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="/history/view/<?= $history['history_id'] ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Rating
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Why Rate?
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Help your barber improve their skills
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Share your experience with others
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Build a better relationship with your barber
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Contribute to the community
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-lightbulb"></i> Rating Tips
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-star text-warning"></i>
                            Consider the overall experience
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-star text-warning"></i>
                            Think about the quality of the haircut
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-star text-warning"></i>
                            Consider the barber's professionalism
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-star text-warning"></i>
                            Rate based on value for money
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rating-stars {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
}

.rating-input {
    display: none;
}

.rating-star {
    cursor: pointer;
    font-size: 2rem;
    color: #ddd;
    transition: color 0.2s;
}

.rating-star:hover,
.rating-star:hover ~ .rating-star,
.rating-input:checked ~ .rating-star {
    color: #ffc107;
}

.rating-labels {
    display: flex;
    justify-content: space-between;
    font-size: 0.8rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.rating-star');
    const inputs = document.querySelectorAll('.rating-input');
    
    stars.forEach((star, index) => {
        star.addEventListener('click', function() {
            // Uncheck all inputs
            inputs.forEach(input => input.checked = false);
            // Check the clicked star
            inputs[index].checked = true;
            
            // Update visual state
            stars.forEach((s, i) => {
                if (i <= index) {
                    s.style.color = '#ffc107';
                } else {
                    s.style.color = '#ddd';
                }
            });
        });
    });
    
    // Initialize visual state based on current selection
    const checkedInput = document.querySelector('.rating-input:checked');
    if (checkedInput) {
        const index = Array.from(inputs).indexOf(checkedInput);
        stars.forEach((star, i) => {
            if (i <= index) {
                star.style.color = '#ffc107';
            }
        });
    }
});
</script>

<?= $this->endSection() ?>
