<?php

declare(strict_types=1);

function current_user(): ?array
{
    static $user;

    if ($user !== null) {
        return $user;
    }

    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        return $user = null;
    }

    $user = db_query('SELECT * FROM users WHERE id = :id LIMIT 1', ['id' => $userId])->fetch();

    return $user ?: null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    return (current_user()['role'] ?? 'user') === 'admin';
}

function require_guest(): void
{
    if (is_logged_in()) {
        redirect(route_url('home'));
    }
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('error', 'Silakan login terlebih dahulu.');
        redirect(route_url('login'));
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        exit('Akses admin ditolak.');
    }
}

function attempt_login(string $email, string $password): bool
{
    $user = db_query('SELECT * FROM users WHERE email = :email LIMIT 1', ['email' => $email])->fetch();
    if (!$user || !password_verify($password, $user['password'])) {
        return false;
    }

    $_SESSION['user_id'] = (int) $user['id'];

    return true;
}

function logout_user(): void
{
    unset($_SESSION['user_id']);
}
