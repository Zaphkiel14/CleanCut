<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="display-5 fw-bold text-primary mb-3">
                <i class="fas fa-cut me-2"></i> CLEAN CUT SUBSCRIPTIONS
            </h1>
            <p class="lead text-muted mb-0">
                Power-up your barbering or shop. Simple, transparent pricing for every professional and shop owner.
            </p>
        </div>
    </div>

    <!-- Barber (Solo) Plans -->
    <div class="row justify-content-center mb-5">
        <div class="col-12 text-center mb-4">
            <h3 class="fw-bold"><i class="fas fa-user"></i> For Barbers (Solo Account)</h3>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-lg text-center">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3 fs-1">🪮</div>
                    <h5 class="card-title fw-bold">Monthly Plan</h5>
                    <p class="card-text text-muted mb-4">
                        Access all features, profile visibility, and booking system
                    </p>
                    <div class="mt-auto">
                        <button type="button"
                                class="btn btn-primary btn-lg w-100 fw-bold rounded-pill mb-2"
                                data-bs-toggle="modal"
                                data-bs-target="#planUnavailableModal">
                            ₱199 / month
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-lg text-center">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3 fs-1">💳</div>
                    <h5 class="card-title fw-bold">Yearly Plan</h5>
                    <p class="card-text text-muted mb-4">
                        2 months free!
                    </p>
                    <div class="mt-auto">
                        <button type="button"
                                class="btn btn-success btn-lg w-100 fw-bold rounded-pill mb-2"
                                data-bs-toggle="modal"
                                data-bs-target="#planUnavailableModal">
                            ₱1,999 / year
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal for unavailable plans -->
        <div class="modal fade" id="planUnavailableModal" tabindex="-1" aria-labelledby="planUnavailableModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="planUnavailableModalLabel">Plan Unavailable</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        Sorry, this subscription option is not available at the moment.<br>
                        <strong>Currently, only the <span class="text-warning">Lifetime Plan</span> is functional.</strong><br>
                        Please choose the Lifetime Plan to proceed. Thank you for your understanding!
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-lg text-center">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3 fs-1">💎</div>
                    <h5 class="card-title fw-bold">Lifetime Plan</h5>
                    <p class="card-text text-muted mb-4">
                        Pay once, lifetime access
                    </p>
                    <div class="mt-auto">
                        <form action="<?= route_to('subplan') ?>" method="post">
                            <input type="hidden" name="name" value="CleanCut Lifetime Barber Subscription">
                            <input type="hidden" name="description" value="Lifetime plan for Barbers">
                            <input type="hidden" name="price" value="499900">
                            <input type="hidden" name="tier" value="solo">
                            <input type="hidden" name="role" value="barber">
                            <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold rounded-pill mb-2">
                                ₱4,999 one-time
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Shop Owner Plans -->
    <div class="row mb-3 pt-2">
        <div class="col-12 text-center mb-4 mt-2">
            <h3 class="fw-bold"><i class="fas fa-store"></i> For Shop Owners</h3>
            <div class="small text-muted mt-2">
                Each plan includes free barber accounts, shop dashboard, booking management, and analytics.
            </div>
        </div>

        <!-- Basic Plan Card -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-lg text-center">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3 fs-1">🧾</div>
                    <h5 class="card-title fw-bold">Basic Plan</h5>
                    <div class="mb-2">
                        <span class="badge bg-secondary rounded-pill mb-2">
                            3 barber accounts included
                        </span>
                    </div>
                    <ul class="list-unstyled mb-4 text-muted">
                        <li>Monthly: <b class="text-primary">₱499</b></li>
                        <li>Yearly: <b class="text-success">₱4,999</b></li>
                        <li>Lifetime: <b class="text-warning">₱11,999</b></li>
                    </ul>
                    <div class="mt-auto">
                        <!-- Choose Basic Plan Button triggers modal or options -->
                        <button class="btn btn-primary btn-lg w-100 fw-bold rounded-pill mb-2" type="button"
                            data-bs-toggle="collapse" data-bs-target="#basicPlanOptions" aria-expanded="false" aria-controls="basicPlanOptions">
                            Choose Basic Plan
                        </button>
                        <div class="collapse pt-2" id="basicPlanOptions">
                            <form action="<?= route_to('subplan') ?>" method="post" class="mb-2">
                                <input type="hidden" name="name" value="CleanCut Monthly Basic Subscription">
                                <input type="hidden" name="description" value="Monthly Basic plan for Owners with 3 Barber accounts">
                                <input type="hidden" name="price" value="49900">
                                <input type="hidden" name="tier" value="1 owner & 3 barbers">
                                <input type="hidden" name="role" value="owner">
                                <button type="submit" class="btn btn-outline-primary w-100">Select Monthly Plan</button>
                            </form>
                            <form action="<?= route_to('subplan') ?>" method="post" class="mb-2">
                                <input type="hidden" name="name" value="CleanCut Yearly Basic Subscription">
                                <input type="hidden" name="description" value="Yearly Basic plan for Owners with 3 Barber accounts">
                                <input type="hidden" name="price" value="499900">
                                <input type="hidden" name="tier" value="1 owner & 3 barbers">
                                <input type="hidden" name="role" value="owner">
                                <button type="submit" class="btn btn-outline-success w-100">Select Yearly Plan</button>
                            </form>
                            <form action="<?= route_to('subplan') ?>" method="post">
                                <input type="hidden" name="name" value="CleanCut Lifetime Basic Subscription">
                                <input type="hidden" name="description" value="Lifetime Basic plan for Owners with 3 Barber accounts">
                                <input type="hidden" name="price" value="1199900">
                                <input type="hidden" name="tier" value="1 owner & 3 barbers">
                                <input type="hidden" name="role" value="owner">
                                <button type="submit" class="btn btn-outline-warning w-100">Select Lifetime Plan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pro Plan Card -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-lg text-center">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3 fs-1">📈</div>
                    <h5 class="card-title fw-bold">Pro Plan</h5>
                    <div class="mb-2">
                        <span class="badge bg-secondary rounded-pill mb-2">
                            6 barber accounts included
                        </span>
                    </div>
                    <ul class="list-unstyled mb-4 text-muted">
                        <li>Monthly: <b class="text-primary">₱899</b></li>
                        <li>Yearly: <b class="text-success">₱8,999</b></li>
                        <li>Lifetime: <b class="text-warning">₱19,999</b></li>
                    </ul>
                    <div class="mt-auto">
                        <!-- Choose Pro Plan Button triggers modal or options -->
                        <button class="btn btn-success btn-lg w-100 fw-bold rounded-pill mb-2" type="button"
                            data-bs-toggle="collapse" data-bs-target="#proPlanOptions" aria-expanded="false" aria-controls="proPlanOptions">
                            Choose Pro Plan
                        </button>
                        <div class="collapse pt-2" id="proPlanOptions">
                            <form action="<?= route_to('subplan') ?>" method="post" class="mb-2">
                                <input type="hidden" name="name" value="CleanCut Monthly Pro Subscription">
                                <input type="hidden" name="description" value="Monthly Pro plan for Owners with 6 Barber accounts">
                                <input type="hidden" name="price" value="89900">
                                <input type="hidden" name="tier" value="1 owner & 6 barbers">
                                <input type="hidden" name="role" value="owner">
                                <button type="submit" class="btn btn-outline-primary w-100">Select Monthly Plan</button>
                            </form>
                            <form action="<?= route_to('subplan') ?>" method="post" class="mb-2">
                                <input type="hidden" name="name" value="CleanCut Yearly Pro Subscription">
                                <input type="hidden" name="description" value="Yearly Pro plan for Owners with 6 Barber accounts">
                                <input type="hidden" name="price" value="899900">
                                <input type="hidden" name="tier" value="1 owner & 6 barbers">
                                <input type="hidden" name="role" value="owner">
                                <button type="submit" class="btn btn-outline-success w-100">Select Yearly Plan</button>
                            </form>
                            <form action="<?= route_to('subplan') ?>" method="post">
                                <input type="hidden" name="name" value="CleanCut Lifetime Pro Subscription">
                                <input type="hidden" name="description" value="Lifetime Pro plan for Owners with 6 Barber accounts">
                                <input type="hidden" name="price" value="1999900">
                                <input type="hidden" name="tier" value="1 owner & 6 barbers">
                                <input type="hidden" name="role" value="owner">
                                <button type="submit" class="btn btn-outline-warning w-100">Select Lifetime Plan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Premium Plan Card -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-0 shadow-lg text-center">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3 fs-1">🏆</div>
                    <h5 class="card-title fw-bold">Premium Plan</h5>
                    <div class="mb-2">
                        <span class="badge bg-secondary rounded-pill mb-2">
                            9 barber accounts included
                        </span>
                    </div>
                    <ul class="list-unstyled mb-4 text-muted">
                        <li>Monthly: <b class="text-primary">₱1,299</b></li>
                        <li>Yearly: <b class="text-success">₱12,999</b></li>
                        <li>Lifetime: <b class="text-warning">₱25,999</b></li>
                    </ul>
                    <div class="mt-auto">
                        <!-- Choose Premium Plan Button triggers modal or options -->
                        <button class="btn btn-warning btn-lg w-100 fw-bold rounded-pill mb-2" type="button"
                            data-bs-toggle="collapse" data-bs-target="#premiumPlanOptions" aria-expanded="false" aria-controls="premiumPlanOptions">
                            Choose Premium Plan
                        </button>
                        <div class="collapse pt-2" id="premiumPlanOptions">
                            <form action="<?= route_to('subplan') ?>" method="post" class="mb-2">
                                <input type="hidden" name="name" value="CleanCut Monthly Premium Subscription">
                                <input type="hidden" name="description" value="Monthly Premium plan for Owners with 9 Barber accounts">
                                <input type="hidden" name="price" value="129900">
                                <input type="hidden" name="tier" value="1 owner & 9 barbers">
                                <input type="hidden" name="role" value="owner">
                                <button type="submit" class="btn btn-outline-primary w-100">Select Monthly Plan</button>
                            </form>
                            <form action="<?= route_to('subplan') ?>" method="post" class="mb-2">
                                <input type="hidden" name="name" value="CleanCut Yearly Premium Subscription">
                                <input type="hidden" name="description" value="Yearly Premium plan for Owners with 9 Barber accounts">
                                <input type="hidden" name="price" value="1299900">
                                <input type="hidden" name="tier" value="1 owner & 9 barbers">
                                <input type="hidden" name="role" value="owner">
                                <button type="submit" class="btn btn-outline-success w-100">Select Yearly Plan</button>
                            </form>
                            <form action="<?= route_to('subplan') ?>" method="post">
                                <input type="hidden" name="name" value="CleanCut Lifetime Premium Subscription">
                                <input type="hidden" name="description" value="Lifetime Premium plan for Owners with 9 Barber accounts">
                                <input type="hidden" name="price" value="2599900">
                                <input type="hidden" name="tier" value="1 owner & 9 barbers">
                                <input type="hidden" name="role" value="owner">
                                <button type="submit" class="btn btn-outline-warning w-100">Select Lifetime Plan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <!-- Add-on Info -->
    <div class="row justify-content-center">
        <div class="col-12 text-center">
            <div class="alert alert-light border-secondary d-inline-block mt-3 mb-2 shadow-sm">
                <span class="fs-5"><i class="fas fa-lightbulb text-warning"></i></span>
                <span class="fw-bold ms-2">Need more barbers?</span>
                <div class="small text-muted pt-1">
                    Each additional barber slot can be purchased separately at <b class="text-dark">₱150/month</b>.
                </div>
            </div>
        </div>
    </div>



</div>
<?= $this->endSection() ?>