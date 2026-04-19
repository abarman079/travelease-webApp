<?php
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config/config.php';
}

function setFlash(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function getFlash(string $key): ?string
{
    if (!empty($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }

    return null;
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['user']['user_id']);
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function currentUserRole(): ?string
{
    return $_SESSION['user']['role_name'] ?? null;
}

function dashboardPathForRole(?string $role): string
{
    switch ($role) {
        case 'traveler':
            return BASE_URL . '/traveler/dashboard.php';
        case 'agent':
            return BASE_URL . '/agent/dashboard.php';
        case 'admin':
            return BASE_URL . '/admin/dashboard.php';
        default:
            return BASE_URL . '/index.php';
    }
}

function redirectTo(string $path): void
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        setFlash('error', 'Please log in first.');
        redirectTo('/login.php');
    }
}

function requireRole(array $roles): void
{
    requireLogin();

    if (!in_array(currentUserRole(), $roles, true)) {
        setFlash('error', 'You do not have permission to access that page.');
        redirectTo('/index.php');
    }
}