<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';

requireRole(['agent']);
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/agent/manage-trips.php');
    exit;
}

$title = trim($_POST['title'] ?? '');
$destination = trim($_POST['destination'] ?? '');
$category = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');
$durationDays = (int) ($_POST['duration_days'] ?? 0);
$price = (float) ($_POST['price'] ?? 0);
$availableSlots = (int) ($_POST['available_slots'] ?? 0);

if (
    $title === '' || $destination === '' || $category === '' ||
    $description === '' || $durationDays <= 0 || $price <= 0 || $availableSlots <= 0
) {
    setFlash('error', 'Please complete all trip fields correctly.');
    header('Location: ' . BASE_URL . '/agent/add-trip.php');
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO trips (
            created_by, title, destination, category, description,
            duration_days, price, available_slots, status
        ) VALUES (
            :created_by, :title, :destination, :category, :description,
            :duration_days, :price, :available_slots, 'active'
        )
    ");
    $stmt->execute([
        'created_by' => $user['user_id'],
        'title' => $title,
        'destination' => $destination,
        'category' => $category,
        'description' => $description,
        'duration_days' => $durationDays,
        'price' => $price,
        'available_slots' => $availableSlots
    ]);

    setFlash('success', 'Trip added successfully.');
    header('Location: ' . BASE_URL . '/agent/manage-trips.php');
    exit;
} catch (PDOException $e) {
    setFlash('error', 'Something went wrong while saving the trip.');
    header('Location: ' . BASE_URL . '/agent/add-trip.php');
    exit;
}