<?php

$secretsFile = __DIR__ . '/secrets.php';
$secrets = file_exists($secretsFile) ? require $secretsFile : [];

$host = $secrets['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
$port = $secrets['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';
$dbname = $secrets['DB_NAME'] ?? getenv('DB_NAME') ?: 'travelease_db';
$username = $secrets['DB_USER'] ?? getenv('DB_USER') ?: 'root';
$password = $secrets['DB_PASS'] ?? getenv('DB_PASS') ?: '';

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed. Please check database settings.");
}
