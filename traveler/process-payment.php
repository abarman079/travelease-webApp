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
$paymentMethod = trim($_POST['payment_method'] ?? '');
$accountNumber = preg_replace('/\D+/', '', $_POST['account_number'] ?? '');
$cvv = preg_replace('/\D+/', '', $_POST['cvv'] ?? '');

$allowedMethods = ['Credit Card', 'Debit Card', 'Bank Transfer', 'Digital Wallet'];

if ($bookingId <= 0 || !in_array($paymentMethod, $allowedMethods, true)) {
    setFlash('error', 'Please complete all payment fields.');
    header('Location: ' . BASE_URL . '/traveler/payment.php?booking_id=' . $bookingId);
    exit;
}

if (!preg_match('/^\d{12,16}$/', $accountNumber)) {
    setFlash('error', 'Account number must contain only digits and be no more than 16 digits.');
    header('Location: ' . BASE_URL . '/traveler/payment.php?booking_id=' . $bookingId);
    exit;
}

if (!preg_match('/^\d{3,4}$/', $cvv)) {
    setFlash('error', 'CVV must be 3 or 4 digits.');
    header('Location: ' . BASE_URL . '/traveler/payment.php?booking_id=' . $bookingId);
    exit;
}

try {
    $bookingStmt = $pdo->prepare("
        SELECT 
            b.booking_id,
            b.user_id,
            b.total_amount,
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

    if ($booking['booking_status'] === 'cancelled') {
        setFlash('error', 'Cancelled bookings cannot be paid.');
        header('Location: ' . BASE_URL . '/traveler/my-bookings.php');
        exit;
    }

    $existingPaymentStmt = $pdo->prepare("
        SELECT payment_id, payment_status
        FROM payments
        WHERE booking_id = :booking_id
        LIMIT 1
    ");
    $existingPaymentStmt->execute(['booking_id' => $bookingId]);
    $existingPayment = $existingPaymentStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingPayment && $existingPayment['payment_status'] === 'paid') {
        setFlash('success', 'Payment was already completed for this booking.');
        header('Location: ' . BASE_URL . '/traveler/my-bookings.php');
        exit;
    }

    $transactionRef = 'TXN-' . strtoupper(bin2hex(random_bytes(4)));

    $pdo->beginTransaction();

    if ($existingPayment) {
        $updatePayment = $pdo->prepare("
            UPDATE payments
            SET amount = :amount,
                payment_method = :payment_method,
                payment_status = 'paid',
                transaction_ref = :transaction_ref,
                paid_at = NOW()
            WHERE booking_id = :booking_id
        ");
        $updatePayment->execute([
            'amount' => $booking['total_amount'],
            'payment_method' => $paymentMethod,
            'transaction_ref' => $transactionRef,
            'booking_id' => $bookingId
        ]);
    } else {
        $insertPayment = $pdo->prepare("
            INSERT INTO payments (booking_id, amount, payment_method, payment_status, transaction_ref, paid_at)
            VALUES (:booking_id, :amount, :payment_method, 'paid', :transaction_ref, NOW())
        ");
        $insertPayment->execute([
            'booking_id' => $bookingId,
            'amount' => $booking['total_amount'],
            'payment_method' => $paymentMethod,
            'transaction_ref' => $transactionRef
        ]);
    }

    $updateBooking = $pdo->prepare("
        UPDATE bookings
        SET booking_status = 'confirmed'
        WHERE booking_id = :booking_id
    ");
    $updateBooking->execute(['booking_id' => $bookingId]);

    $insertNotification = $pdo->prepare("
        INSERT INTO notifications (user_id, message, type)
        VALUES (:user_id, :message, :type)
    ");
    $insertNotification->execute([
        'user_id' => $user['user_id'],
        'message' => 'Your payment was completed successfully and booking #' . $bookingId . ' is now confirmed.',
        'type' => 'payment'
    ]);

    $pdo->commit();

    $agentEmail = getTripAgentEmail($pdo, (int) $booking['trip_id']);
    sendTripConfirmedEmails($pdo, [
        'traveler_email' => $booking['traveler_email'],
        'traveler_name' => $booking['traveler_name'],
        'trip_title' => $booking['title'],
        'destination' => $booking['destination'],
        'booking_id' => $bookingId,
        'travel_date' => $booking['travel_date'],
        'agent_email' => $agentEmail
    ]);

    setFlash('success', 'Mock payment completed successfully. Your booking is now confirmed.');
    header('Location: ' . BASE_URL . '/traveler/my-bookings.php');
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    setFlash('error', 'Something went wrong while processing the payment.');
    header('Location: ' . BASE_URL . '/traveler/payment.php?booking_id=' . $bookingId);
    exit;
}