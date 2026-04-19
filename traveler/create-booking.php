<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';

requireRole(['traveler']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/trips.php');
    exit;
}

$user = currentUser();

$tripId = (int) ($_POST['trip_id'] ?? 0);
$travelDate = trim($_POST['travel_date'] ?? '');
$travelerCount = (int) ($_POST['traveler_count'] ?? 0);

if ($tripId <= 0 || $travelDate === '' || $travelerCount <= 0) {
    setFlash('error', 'Please complete all booking fields.');
    header('Location: ' . BASE_URL . '/trips.php');
    exit;
}

try {
    $tripStmt = $pdo->prepare("
        SELECT trip_id, title, price, available_slots, status
        FROM trips
        WHERE trip_id = :trip_id
        LIMIT 1
    ");
    $tripStmt->execute(['trip_id' => $tripId]);
    $trip = $tripStmt->fetch(PDO::FETCH_ASSOC);

    if (!$trip || $trip['status'] !== 'active') {
        setFlash('error', 'Selected trip is not available.');
        header('Location: ' . BASE_URL . '/trips.php');
        exit;
    }

    if ($travelerCount > (int) $trip['available_slots']) {
        setFlash('error', 'Requested traveler count exceeds available seats.');
        header('Location: ' . BASE_URL . '/traveler/book-trip.php?id=' . $tripId);
        exit;
    }

    $today = date('Y-m-d');
    if ($travelDate < $today) {
        setFlash('error', 'Travel date cannot be in the past.');
        header('Location: ' . BASE_URL . '/traveler/book-trip.php?id=' . $tripId);
        exit;
    }

    $totalAmount = (float) $trip['price'] * $travelerCount;

    $insertStmt = $pdo->prepare("
        INSERT INTO bookings (user_id, trip_id, travel_date, traveler_count, total_amount, booking_status)
        VALUES (:user_id, :trip_id, :travel_date, :traveler_count, :total_amount, 'pending')
    ");

    $insertStmt->execute([
        'user_id' => $user['user_id'],
        'trip_id' => $tripId,
        'travel_date' => $travelDate,
        'traveler_count' => $travelerCount,
        'total_amount' => $totalAmount
    ]);

    $newBookingId = (int) $pdo->lastInsertId();

    setFlash('success', 'Booking created successfully. Please complete payment.');
    header('Location: ' . BASE_URL . '/traveler/payment.php?booking_id=' . $newBookingId);
    exit;
} catch (PDOException $e) {
    setFlash('error', 'Something went wrong while creating the booking.');
    header('Location: ' . BASE_URL . '/traveler/book-trip.php?id=' . $tripId);
    exit;
}