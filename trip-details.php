<?php
require_once __DIR__ . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/navbar.php';

$tripId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT trip_id, title, destination, category, description, duration_days, price, available_slots, status
                       FROM trips
                       WHERE trip_id = :trip_id AND status = 'active'
                       LIMIT 1");
$stmt->execute(['trip_id' => $tripId]);
$trip = $stmt->fetch(PDO::FETCH_ASSOC);

function getTripTheme(string $destination): array
{
    $destination = strtolower($destination);

    if (strpos($destination, 'london') !== false) {
        return [
            'class' => 'theme-london',
            'kicker' => 'Curated London Escape',
            'image' => 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?auto=format&fit=crop&w=1600&q=80'
        ];
    }

    if (strpos($destination, 'cotswolds') !== false) {
        return [
            'class' => 'theme-cotswolds',
            'kicker' => 'Elegant Countryside Retreat',
            'image' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1600&q=80'
        ];
    }

    if (strpos($destination, 'lake') !== false) {
        return [
            'class' => 'theme-lake',
            'kicker' => 'Refined Nature Journey',
            'image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1600&q=80'
        ];
    }

    if (strpos($destination, 'bath') !== false) {
        return [
            'class' => 'theme-bath',
            'kicker' => 'Heritage and Stone Elegance',
            'image' => 'https://images.unsplash.com/photo-1518005020951-eccb494ad742?auto=format&fit=crop&w=1600&q=80'
        ];
    }

    if (strpos($destination, 'york') !== false) {
        return [
            'class' => 'theme-york',
            'kicker' => 'Historic City Discovery',
            'image' => 'https://images.unsplash.com/photo-1501594907352-04cda38ebc29?auto=format&fit=crop&w=1600&q=80'
        ];
    }

    if (strpos($destination, 'cornwall') !== false) {
        return [
            'class' => 'theme-cornwall',
            'kicker' => 'Coastal Luxury Hideaway',
            'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1600&q=80'
        ];
    }

    return [
        'class' => 'theme-default',
        'kicker' => 'Curated Travel Experience',
        'image' => 'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=1600&q=80'
    ];
}
?>

<?php if (!$trip): ?>
    <div class="container py-5">
        <div class="alert alert-warning soft-panel p-4 border-0">
            Trip not found or unavailable.
        </div>
    </div>
<?php else: ?>
    <?php $theme = getTripTheme($trip['destination']); ?>

    <section class="trip-hero <?= htmlspecialchars($theme['class']); ?>">
        <div class="trip-hero-media">
            <img src="<?= htmlspecialchars($theme['image']); ?>" alt="<?= htmlspecialchars($trip['destination']); ?> travel view">
            <div class="trip-hero-overlay"></div>
        </div>

        <div class="container position-relative">
            <div class="trip-hero-content">
                <div class="trip-hero-kicker mb-3"><?= htmlspecialchars($theme['kicker']); ?></div>
                <h1 class="trip-hero-title mb-3"><?= htmlspecialchars($trip['title']); ?></h1>
                <p class="trip-hero-text mb-4">
                    Discover a premium England-focused travel experience designed with comfort,
                    style, and thoughtful itinerary planning in mind.
                </p>

                <div class="trip-hero-meta d-flex flex-wrap gap-3">
                    <span class="hero-pill"><?= htmlspecialchars($trip['destination']); ?></span>
                    <span class="hero-pill"><?= htmlspecialchars($trip['category']); ?></span>
                    <span class="hero-pill"><?= (int) $trip['duration_days']; ?> days</span>
                    <span class="hero-pill"><?= (int) $trip['available_slots']; ?> seats left</span>
                </div>
            </div>
        </div>
    </section>

    <div class="container py-5">
        <div class="row g-4 align-items-start">
            <div class="col-lg-8">
                <div class="soft-panel trip-detail-panel p-4 p-md-5 mb-4">
                    <div class="section-kicker mb-2">Overview</div>
                    <h2 class="mb-3">About this journey</h2>
                    <p class="mb-0">
                        <?= nl2br(htmlspecialchars($trip['description'])); ?>
                    </p>
                </div>

                <div class="soft-panel trip-detail-panel p-4 p-md-5 mb-4">
                    <div class="section-kicker mb-2">What to expect</div>
                    <h2 class="mb-4">Trip highlights</h2>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="detail-mini-card">
                                <h6>Curated experience</h6>
                                <p class="mb-0">A premium package with a clean, organized journey plan and destination-focused travel flow.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-mini-card">
                                <h6>Flexible planning</h6>
                                <p class="mb-0">This trip will later connect smoothly with your booking history, itinerary section, and payment flow.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-mini-card">
                                <h6>England-inspired design</h6>
                                <p class="mb-0">The page styling adapts visually to the destination to create a stronger place-based travel feel.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-mini-card">
                                <h6>Responsive layout</h6>
                                <p class="mb-0">The design is structured to remain readable and balanced across desktop, tablet, and mobile screens.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="soft-panel trip-detail-panel p-4 p-md-5">
                    <div class="section-kicker mb-2">Travel note</div>
                    <h2 class="mb-3">Weather and travel insight</h2>
                    <div class="weather-card">
                        <div>
                            <h6 class="mb-2">Weather widget area</h6>
                            <p class="mb-0">
                                In the next phase, we can connect a small professional weather widget here for
                                destination-based UK travel planning.
                            </p>
                        </div>
                        <div class="weather-badge">Coming next</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="soft-panel booking-side-panel p-4 p-md-4 sticky-booking-card">
                    <div class="section-kicker mb-2">Booking summary</div>
                    <h3 class="mb-3">Trip Snapshot</h3>

                    <div class="booking-info-list mb-4">
                        <div class="booking-info-row">
                            <span>Destination</span>
                            <strong><?= htmlspecialchars($trip['destination']); ?></strong>
                        </div>
                        <div class="booking-info-row">
                            <span>Category</span>
                            <strong><?= htmlspecialchars($trip['category']); ?></strong>
                        </div>
                        <div class="booking-info-row">
                            <span>Duration</span>
                            <strong><?= (int) $trip['duration_days']; ?> days</strong>
                        </div>
                        <div class="booking-info-row">
                            <span>Availability</span>
                            <strong><?= (int) $trip['available_slots']; ?> seats</strong>
                        </div>
                    </div>

                    <div class="price-box mb-4">
                        <div class="price-label">Starting from</div>
                        <div class="price-value">£<?= number_format($trip['price'], 2); ?></div>
                    </div>

                    <div class="d-grid gap-3">
                        <a href="<?= (isLoggedIn() && currentUserRole() === 'traveler')
                                        ? BASE_URL . '/traveler/book-trip.php?id=' . (int) $trip['trip_id']
                                        : BASE_URL . '/login.php'; ?>" class="btn btn-dark-soft">
                            Continue to Booking
                        </a>
                        <a href="<?= BASE_URL; ?>/trips.php" class="btn btn-hollow">Back to Trips</a>
                    </div>

                    <p class="side-note mt-4 mb-0">
                        Booking flow, payment, and itinerary creation will connect here in the next core feature phase.
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>