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

$itineraryId = (int) ($_POST['itinerary_id'] ?? 0);
$bookingId = (int) ($_POST['booking_id'] ?? 0);
$dayNumber = (int) ($_POST['day_number'] ?? 0);
$itemTitle = trim($_POST['item_title'] ?? '');
$location = trim($_POST['location'] ?? '');
$activityTime = trim($_POST['activity_time'] ?? '');
$itemNotes = trim($_POST['item_notes'] ?? '');

if ($itineraryId <= 0 || $bookingId <= 0 || $dayNumber <= 0 || $itemTitle === '') {
    setFlash('error', 'Please complete all required itinerary item fields.');
    header('Location: ' . BASE_URL . '/traveler/itinerary.php?booking_id=' . $bookingId);
    exit;
}

try {
    $checkStmt = $pdo->prepare("
        SELECT 
            i.itinerary_id,
            b.booking_id,
            b.booking_status,
            t.duration_days
        FROM itineraries i
        INNER JOIN bookings b ON i.booking_id = b.booking_id
        INNER JOIN trips t ON b.trip_id = t.trip_id
        WHERE i.itinerary_id = :itinerary_id
          AND b.booking_id = :booking_id
          AND b.user_id = :user_id
        LIMIT 1
    ");
    $checkStmt->execute([
        'itinerary_id' => $itineraryId,
        'booking_id' => $bookingId,
        'user_id' => $user['user_id']
    ]);
    $row = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        setFlash('error', 'Itinerary not found.');
        header('Location: ' . BASE_URL . '/traveler/my-bookings.php');
        exit;
    }

    if ($row['booking_status'] !== 'confirmed') {
        setFlash('error', 'Only confirmed bookings can have itinerary items.');
        header('Location: ' . BASE_URL . '/traveler/my-bookings.php');
        exit;
    }

    if ($dayNumber > (int) $row['duration_days']) {
        setFlash('error', 'Day number cannot exceed trip duration.');
        header('Location: ' . BASE_URL . '/traveler/itinerary.php?booking_id=' . $bookingId);
        exit;
    }

    $insertStmt = $pdo->prepare("
        INSERT INTO itinerary_items (itinerary_id, day_number, item_title, location, activity_time, notes)
        VALUES (:itinerary_id, :day_number, :item_title, :location, :activity_time, :notes)
    ");
    $insertStmt->execute([
        'itinerary_id' => $itineraryId,
        'day_number' => $dayNumber,
        'item_title' => $itemTitle,
        'location' => $location,
        'activity_time' => $activityTime,
        'notes' => $itemNotes
    ]);

    setFlash('success', 'Itinerary item added successfully.');
    header('Location: ' . BASE_URL . '/traveler/itinerary.php?booking_id=' . $bookingId);
    exit;
} catch (PDOException $e) {
    setFlash('error', 'Something went wrong while adding the itinerary item.');
    header('Location: ' . BASE_URL . '/traveler/itinerary.php?booking_id=' . $bookingId);
    exit;
}