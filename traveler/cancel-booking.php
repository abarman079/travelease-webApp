<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/mailer.php';

requireRole(['traveler']);
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/traveler/my-bookings.php');
    exit;
}

$bookingId = (int) ($_POST['booking_id'] ?? 0);

if ($bookingId <= 0) {
    setFlash('error', 'Invalid cancellation request.');
    header('Location: ' . BASE_URL . '/traveler/my-bookings.php');
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            b.booking_id,
            b.booking_status,
            b.travel_date,
            t.trip_id,
            t.title,
            t.destination,
            u.email AS traveler_email,
            u.full_name AS traveler_name
        FROM bookings b
        INNER JOIN trips t ON b.trip_id = t.trip_id
        INNER JOIN users u ON b.user_id = u.user_id
        WHERE b.booking_id = :booking_id AND b.user_id = :user_id
        LIMIT 1
    ");
    $stmt->execute([
        'booking_id' => $bookingId,
        'user_id' => $user['user_id']
    ]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        setFlash('error', 'Booking not found.');
        header('Location: ' . BASE_URL . '/traveler/my-bookings.php');
        exit;
    }

    if ($booking['booking_status'] === 'cancelled') {
        setFlash('success', 'This booking is already cancelled.');
        header('Location: ' . BASE_URL . '/traveler/my-bookings.php');
        exit;
    }

    if (!in_array($booking['booking_status'], ['pending', 'confirmed'], true)) {
        setFlash('error', 'This booking cannot be cancelled.');
        header('Location: ' . BASE_URL . '/traveler/my-bookings.php');
        exit;
    }

    $pdo->beginTransaction();

    $updateBooking = $pdo->prepare("
        UPDATE bookings
        SET booking_status = 'cancelled'
        WHERE booking_id = :booking_id
    ");
    $updateBooking->execute(['booking_id' => $bookingId]);

    $insertNotification = $pdo->prepare("
        INSERT INTO notifications (user_id, message, type)
        VALUES (:user_id, :message, :type)
    ");
    $insertNotification->execute([
        'user_id' => $user['user_id'],
        'message' => 'Your booking #' . $bookingId . ' has been cancelled successfully.',
        'type' => 'booking'
    ]);

    $pdo->commit();

    $agentEmail = getTripAgentEmail($pdo, (int) $booking['trip_id']);
    sendTripCancelledEmails($pdo, [
        'traveler_email' => $booking['traveler_email'],
        'traveler_name' => $booking['traveler_name'],
        'trip_title' => $booking['title'],
        'destination' => $booking['destination'],
        'booking_id' => $bookingId,
        'travel_date' => $booking['travel_date'],
        'agent_email' => $agentEmail
    ]);

    setFlash('success', 'Booking cancelled successfully.');
    header('Location: ' . BASE_URL . '/traveler/my-bookings.php');
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    setFlash('error', 'Something went wrong while cancelling the booking.');
    header('Location: ' . BASE_URL . '/traveler/my-bookings.php');
    exit;
}