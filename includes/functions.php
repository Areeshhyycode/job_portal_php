<?php
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ----------------------------- URLs / output ----------------------------- */

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

/** Escape for HTML output. */
function e(?string $v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

/* ------------------------------- Auth state ------------------------------ */

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $cached = null;
    if ($cached === null) {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $cached = $stmt->fetch() ?: null;
    }
    return $cached;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function user_role(): ?string
{
    $u = current_user();
    return $u['role'] ?? null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('error', 'Please log in to continue.');
        redirect('auth/login.php');
    }
}

function require_role(string $role): void
{
    require_login();
    if (user_role() !== $role) {
        http_response_code(403);
        die('<p style="font-family:sans-serif;padding:40px">403 — You do not have access to this page.</p>');
    }
}

/* --------------------------------- Flash --------------------------------- */

function flash(string $key, ?string $msg = null): ?string
{
    if ($msg !== null) {
        $_SESSION['flash'][$key] = $msg;
        return null;
    }
    $val = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $val;
}

/* --------------------------------- CSRF ---------------------------------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        die('Invalid CSRF token. Go back and try again.');
    }
}

/* ------------------------------ Misc helpers ----------------------------- */

function salary_range(?int $min, ?int $max): string
{
    if (!$min && !$max) return 'Negotiable';
    $fmt = fn($n) => 'PKR ' . number_format($n);
    if ($min && $max) return $fmt($min) . ' – ' . $fmt($max);
    return $fmt($min ?: $max);
}

function time_ago(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff / 60) . 'm ago';
    if ($diff < 86400)  return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', strtotime($datetime));
}

/** Deterministic pastel colour for company avatars. */
function avatar_color(string $seed): string
{
    $colors = ['#6366f1', '#0ea5e9', '#14b8a6', '#f59e0b', '#ec4899', '#8b5cf6', '#ef4444', '#22c55e'];
    return $colors[abs(crc32($seed)) % count($colors)];
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $out = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $out .= strtoupper($p[0] ?? '');
    }
    return $out ?: '?';
}
