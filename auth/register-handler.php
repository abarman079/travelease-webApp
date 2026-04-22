<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectTo('/register.php');
}

$username = strtolower(trim($_POST['username'] ?? ''));
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$countryCode = trim($_POST['country_code'] ?? '');
$phoneLocal = preg_replace('/\D+/', '', $_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if (
    $username === '' || $firstName === '' || $lastName === '' ||
    $email === '' || $countryCode === '' || $phoneLocal === '' ||
    $password === '' || $confirmPassword === ''
) {
    setFlash('error', 'Please fill in all fields.');
    redirectTo('/register.php');
}

if (!preg_match('/^[a-zA-Z0-9._]{3,30}$/', $username)) {
    setFlash('error', 'Username must be 3 to 30 characters and use only letters, numbers, dots, or underscores.');
    redirectTo('/register.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('error', 'Please enter a valid email address.');
    redirectTo('/register.php');
}

if (!preg_match('/^\+\d{1,4}$/', $countryCode)) {
    setFlash('error', 'Please choose a valid country code.');
    redirectTo('/register.php');
}

if (strlen($phoneLocal) < 6 || strlen($phoneLocal) > 15) {
    setFlash('error', 'Please enter a valid phone number.');
    redirectTo('/register.php');
}

if (strlen($password) < 6) {
    setFlash('error', 'Password must be at least 6 characters long.');
    redirectTo('/register.php');
}

if ($password !== $confirmPassword) {
    setFlash('error', 'Passwords do not match.');
    redirectTo('/register.php');
}

$fullPhone = $countryCode . $phoneLocal;
$fullName = trim($firstName . ' ' . $lastName);

try {
    $checkStmt = $pdo->prepare("
        SELECT user_id
        FROM users
        WHERE email = :email OR username = :username OR phone = :phone
        LIMIT 1
    ");
    $checkStmt->execute([
        'email' => $email,
        'username' => $username,
        'phone' => $fullPhone
    ]);

    if ($checkStmt->fetch()) {
        setFlash('error', 'Email, username, or phone number already exists.');
        redirectTo('/register.php');
    }

    $roleStmt = $pdo->query("SELECT role_id FROM roles WHERE role_name = 'traveler' LIMIT 1");
    $travelerRoleId = $roleStmt->fetchColumn();

    if (!$travelerRoleId) {
        setFlash('error', 'Traveler role is missing in the database.');
        redirectTo('/register.php');
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $insertStmt = $pdo->prepare("
        INSERT INTO users (
            role_id, username, full_name, first_name, last_name,
            email, phone, country_code, password_hash, status
        ) VALUES (
            :role_id, :username, :full_name, :first_name, :last_name,
            :email, :phone, :country_code, :password_hash, 'active'
        )
    ");

    $insertStmt->execute([
        'role_id' => $travelerRoleId,
        'username' => $username,
        'full_name' => $fullName,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'phone' => $fullPhone,
        'country_code' => $countryCode,
        'password_hash' => $passwordHash
    ]);

    sendRegistrationEmails($pdo, $email, $fullName, $username);

    setFlash('success', 'Account created successfully. Please log in.');
    redirectTo('/login.php');
} catch (PDOException $e) {
    setFlash('error', 'Something went wrong while creating the account.');
    redirectTo('/register.php');
}