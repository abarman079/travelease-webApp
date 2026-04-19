<footer class="site-footer mt-5">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-md-4">
                <h5 class="footer-title">TravelEase</h5>
                <p class="footer-text">
                    A refined travel booking and itinerary platform designed for curated UK journeys,
                    premium planning, and trusted customer support.
                </p>
                <div class="trust-badges d-flex flex-wrap gap-2 mt-3">
                    <span class="trust-badge">Visa</span>
                    <span class="trust-badge">Mastercard</span>
                    <span class="trust-badge">Protected</span>
                    <span class="trust-badge">Secure Booking</span>
                </div>
            </div>

            <div class="col-6 col-md-2">
                <h6 class="footer-heading">Company</h6>
                <ul class="footer-links">
                    <li><a href="<?= BASE_URL; ?>/about.php">About</a></li>
                    <li><a href="<?= BASE_URL; ?>/trips.php">Trips</a></li>
                    <li><a href="<?= BASE_URL; ?>/contact.php">Support</a></li>
                </ul>
            </div>

            <div class="col-6 col-md-3">
                <h6 class="footer-heading">Destinations</h6>
                <ul class="footer-links">
                    <li><a href="<?= BASE_URL; ?>/trips.php">London</a></li>
                    <li><a href="<?= BASE_URL; ?>/trips.php">Cotswolds</a></li>
                    <li><a href="<?= BASE_URL; ?>/trips.php">Lake District</a></li>
                    <li><a href="<?= BASE_URL; ?>/trips.php">Bath</a></li>
                </ul>
            </div>

            <div class="col-md-3">
                <h6 class="footer-heading">Support</h6>
                <ul class="footer-links">
                    <li><a href="<?= BASE_URL; ?>/contact.php">Help Desk</a></li>
                    <li><a href="<?= BASE_URL; ?>/login.php">Account Access</a></li>
                    <li><a href="<?= BASE_URL; ?>/register.php">Create Account</a></li>
                </ul>
            </div>
        </div>

        <hr class="footer-divider my-4">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <p class="mb-0 footer-copy">© <?= date("Y"); ?> TravelEase. All rights reserved.</p>
            <p class="mb-0 footer-copy">Designed for curated UK travel experiences.</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>