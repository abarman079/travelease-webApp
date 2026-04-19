<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectTo('/login.php');
}

$identifier = trim($_POST['identifier'] ?? '');
$password = $_POST['password'] ?? '';

if ($identifier === '' || $password === '') {
    setFlash('error', 'Please enter your username, email, or phone number, and your password.');
    redirectTo('/login.php');
}

$phoneFull = preg_replace('/[^\d+]/', '', $identifier);
$phoneDigits = preg_replace('/\D+/', '', $identifier);

try {
    $stmt = $pdo->prepare("
        SELECT 
            u.user_id, u.username, u.full_name, u.first_name, u.last_name,
            u.email, u.phone, u.country_code, u.password_hash, u.status, r.role_name
        FROM users u
        INNER JOIN roles r ON u.role_id = r.role_id
        WHERE 
            u.email = :identifier
            OR u.username = :identifier
            OR u.phone = :phone_full
            OR REPLACE(u.phone, '+', '') = :phone_digits
        LIMIT 1
    ");
    $stmt->execute([
        'identifier' => $identifier,
        'phone_full' => $phoneFull,
        'phone_digits' => $phoneDigits
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        setFlash('error', 'Invalid username, email, phone number, or password.');
        redirectTo('/login.php');
    }

    if ($user['status'] !== 'active') {
        setFlash('error', 'This account is not active.');
        redirectTo('/login.php');
    }

    session_regenerate_id(true);

    $_SESSION['user'] = [
        'user_id' => $user['user_id'],
        'username' => $user['username'],
        'full_name' => $user['full_name'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'email' => $user['email'],
        'phone' => $user['phone'],
        'country_code' => $user['country_code'],
        'role_name' => $user['role_name']
    ];

    header('Location: ' . dashboardPathForRole($user['role_name']));
    exit;
} catch (PDOException $e) {
    setFlash('error', 'Something went wrong while logging in.');
    redirectTo('/login.php');
}