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
$paymentMethod = trim($_POST['payment_method'] ?? '');
$mockAccount = trim($_POST['mock_account'] ?? '');
$securityCode = trim($_POST['security_code'] ?? '');

if ($bookingId <= 0 || $paymentMethod === '' || $mockAccount === '' || $securityCode === '') {
    setFlash('error', 'Please complete all payment fields.');
    header('Location: ' . BASE_URL . '/traveler/payment.php?booking_id=' . $bookingId);
    exit;
}

try {
    $bookingStmt = $pdo->prepare("
        SELECT booking_id, user_id, total_amount, booking_status
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