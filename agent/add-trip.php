<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/includes/auth.php';

requireRole(['agent']);

require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="soft-panel p-4 p-md-5">
                <div class="section-kicker mb-2">Agent tools</div>
                <h1 class="mb-3">Add New Trip</h1>
                <p class="mb-4">Create a new travel offer for the TravelEase platform.</p>

                <form method="POST" action="<?= BASE_URL; ?>/agent/save-trip.php" class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label auth-label">Trip Title</label>
                        <input type="text" name="title" class="form-control auth-input" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label auth-label">Category</label>
                        <input type="text" name="category" class="form-control auth-input" placeholder="e.g. City Break" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label auth-label">Destination</label>
                        <input type="text" name="destination" class="form-control auth-input" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label auth-label">Description</label>
                        <textarea name="description" rows="4" class="form-control auth-input" required></textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label auth-label">Duration (days)</label>
                        <input type="number" name="duration_days" min="1" class="form-control auth-input" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label auth-label">Price (£)</label>
                        <input type="number" step="0.01" name="price" min="0" class="form-control auth-input" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label auth-label">Available Seats</label>
                        <input type="number" name="available_slots" min="1" class="form-control auth-input" required>
                    </div>

                    <div class="col-12 d-grid mt-3">
                        <button type="submit" class="btn btn-dark-soft">Save Trip</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>