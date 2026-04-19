<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'TravelEase');
define('ROOT_PATH', dirname(__DIR__));

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

$isLocal =
    str_starts_with($host, 'localhost') ||
    str_starts_with($host, '127.0.0.1');

define('APP_ENV', $isLocal ? 'local' : 'production');

/*
|--------------------------------------------------------------------------
| BASE_URL
|--------------------------------------------------------------------------
| Local: project runs at http://localhost/travelease
| Live: if uploaded to web root, keep BASE_URL as ''
| If uploaded to a subfolder on live hosting, change '' to '/foldername'
|--------------------------------------------------------------------------
*/
define('BASE_URL', $isLocal ? '/travelease' : '');