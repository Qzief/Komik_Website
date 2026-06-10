<?php

declare(strict_types=1);

function env_value(string $key, ?string $default = null): ?string
{
    return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
}

function base_url(string $path = ''): string
{
    $base = rtrim((string) config('app.url'), '/');
    $path = ltrim($path, '/');

    return $path === '' ? $base : $base . '/' . $path;
}

function asset_url(string $path): string
{
    return base_url($path);
}

function site_icon_url(): string
{
    return asset_url('icon.png');
}

function config(string $key, mixed $default = null): mixed
{
    static $config;

    if ($config === null) {
        $config = require __DIR__ . '/../config/config.php';
    }

    $segments = explode('.', $key);
    $value = $config;

    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }

        $value = $value[$segment];
    }

    return $value;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function current_route(): string
{
    return $_GET['route'] ?? 'home';
}

function is_post(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function flash(string $key, mixed $value): void
{
    $_SESSION['_flash'][$key] = $value;
}

function flash_get(string $key, mixed $default = null): mixed
{
    if (!isset($_SESSION['_flash'][$key])) {
        return $default;
    }

    $value = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);

    return $value;
}

function validation_errors(): array
{
    return $_SESSION['_errors'] ?? [];
}

function set_validation_errors(array $errors): void
{
    $_SESSION['_errors'] = $errors;
}

function clear_form_state(): void
{
    unset($_SESSION['_old'], $_SESSION['_errors']);
}

function remember_input(array $input): void
{
    $_SESSION['_old'] = $input;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function verify_csrf(): void
{
    $token = $_POST['_token'] ?? '';

    if (!hash_equals($_SESSION['_csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('CSRF token tidak valid.');
    }
}

function format_number(int $value): string
{
    return number_format($value, 0, ',', '.');
}

function format_datetime(?string $value): string
{
    if (!$value) {
        return '-';
    }

    $timestamp = strtotime($value);

    return $timestamp ? date('d M Y H:i', $timestamp) : $value;
}

function str_limit(?string $value, int $limit = 160): string
{
    $value = trim((string) $value);

    if (mb_strlen($value) <= $limit) {
        return $value;
    }

    return mb_substr($value, 0, $limit - 3) . '...';
}

function route_url(string $route, array $params = []): string
{
    $params = array_merge(['route' => $route], $params);

    return base_url('index.php?' . http_build_query($params));
}

function request_int(string $key, int $default = 0): int
{
    $value = $_GET[$key] ?? $_POST[$key] ?? $default;

    return max(0, (int) $value);
}

function request_user_agent(): string
{
    return strtolower(trim((string) ($_SERVER['HTTP_USER_AGENT'] ?? '')));
}

function is_mobile_request(): bool
{
    $userAgent = request_user_agent();

    if ($userAgent === '') {
        return false;
    }

    return preg_match('/android|iphone|ipod|ipad|mobile|opera mini|iemobile|blackberry|webos|phone/i', $userAgent) === 1;
}

function home_catalog_per_page(): int
{
    return is_mobile_request() ? 26 : 28;
}

function render(string $view, array $data = [], ?string $title = null): void
{
    extract($data, EXTR_SKIP);
    ob_start();
    require dirname(__DIR__) . '/views/' . $view . '.php';
    $content = (string) ob_get_clean();
    require dirname(__DIR__) . '/views/layout.php';
}

function back_url(string $defaultRoute = 'home'): string
{
    $referer = $_SERVER['HTTP_REFERER'] ?? '';

    return $referer !== '' ? $referer : route_url($defaultRoute);
}

function proxy_image_url(?string $url): string
{
    $url = trim((string) $url);
    if ($url === '') {
        return 'https://placehold.co/690x1000/111214/cfd3da?text=Komik';
    }

    return route_url('image.proxy', ['url' => $url]);
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
