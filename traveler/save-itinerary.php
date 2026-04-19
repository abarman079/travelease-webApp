<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';

requireRole(['traveler']);
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/traveler/my-bookings.php');
    exit;
}

$bookingId = (int) ($_POST['booking_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$notes = trim($_POST['notes'] ?? '');

if ($bookingId <= 0 || $title === '') {
    setFlash('error', 'Please provide an itinerary title.');
    header('Location: ' . BASE_URL . '/traveler/itinerary.php?booking_id=' . $bookingId);
    exit;
}

try {
    $bookingStmt = $pdo->prepare("
        SELECT booking_id, booking_status
        FROM bookings
        WHERE booking_id = :booking_id AND user_id = :user_id
        LIMIT 1
    ");
    $bookingStmt->execute([
        'booking_id' => $bookingId,
        'user_id' => $user['user_id']
    ]);
    $booking = $bookingStmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        setFlash('error', 'Booking not found.');
        header('Location: ' . BASE_URL . '/traveler/my-bookings.php');
        exit;
    }

    if ($booking['booking_status'] !== 'confirmed') {
        setFlash('error', 'You can plan a trip only after the booking is confirmed.');
        header('Location: ' . BASE_URL . '/traveler/my-bookings.php');
        exit;
    }

    $checkStmt = $pdo->prepare("
        SELECT itinerary_id
        FROM itineraries
        WHERE booking_id = :booking_id
        LIMIT 1
    ");
    $checkStmt->execute(['booking_id' => $bookingId]);
    $existingItineraryId = $checkStmt->fetchColumn();

    if ($existingItineraryId) {
        $updateStmt = $pdo->prepare("
            UPDATE itineraries
            SET title = :title, notes = :notes
            WHERE booking_id = :booking_id
        ");
        $updateStmt->execute([
            'title' => $title,
            'notes' => $notes,
            'booking_id' => $bookingId
        ]);
        setFlash('success', 'Itinerary updated successfully.');
    } else {
        $insertStmt = $pdo->prepare("
            INSERT INTO itineraries (booking_id, title, notes)
            VALUES (:booking_id, :title, :notes)
        ");
        $insertStmt->execute([
            'booking_id' => $bookingId,
            'title' => $title,
            'notes' => $notes
        ]);
        setFlash('success', 'Itinerary created successfully.');
    }

    header('Location: ' . BASE_URL . '/traveler/itinerary.php?booking_id=' . $bookingId);
    exit;
} catch (PDOException $e) {
    setFlash('error', 'Something went wrong while saving the itinerary.');
    header('Location: ' . BASE_URL . '/traveler/itinerary.php?booking_id=' . $bookingId);
    exit;
}