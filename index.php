<?php
require_once __DIR__ . '/config/config.php';
require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';
?>

<section class="container py-5">
    <div class="hero-panel">
        <div class="hero-copy">
            <p class="hero-kicker">Curated England Journeys</p>
            <h1 class="hero-title">Welcome to TravelEase</h1>
            <p class="hero-text">
                A refined travel booking, payment, and itinerary platform designed for curated UK journeys,
                premium planning, and trusted customer support.
            </p>
            <div class="hero-actions">
                <a href="<?= BASE_URL; ?>/trips.php" class="btn btn-dark-soft">Explore Trips</a>
                <a href="<?= BASE_URL; ?>/about.php" class="btn btn-hollow">Learn More</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>