<?php
// helpers/auth.php — centralized authorization helpers
// Usage: include 'helpers/auth.php'; then call require_login() or require_role('Admin') etc.

if (session_status() === PHP_SESSION_NONE) session_start();

function is_logged(): bool {
    return isset($_SESSION['id_users']) && isset($_SESSION['role']);
}
function current_role(): ?string {
    return $_SESSION['role'] ?? null;
}
function is_admin(): bool {
    return is_logged() && $_SESSION['role'] === 'Admin';
}
function is_jurnalis(): bool {
    return is_logged() && $_SESSION['role'] === 'Jurnalis';
}
function is_admin_or_jurnalis(): bool {
    return is_logged() && in_array($_SESSION['role'], ['Admin','Jurnalis'], true);
}
function require_login(string $redirect = 'login.php'): void {
    if (!is_logged()) {
        header("Location: $redirect?error=".urlencode('Silakan login terlebih dahulu'));
        exit;
    }
}
function require_role(string|array $roles, string $redirect = 'login.php'): void {
    if (!is_logged()) {
        header("Location: $redirect?error=".urlencode('Silakan login terlebih dahulu'));
        exit;
    }
    $allowed = is_array($roles) ? $roles : [$roles];
    if (!in_array($_SESSION['role'], $allowed, true)) {
        // Jurnalis coba akses Admin atau Pembaca coba akses Jurnalis
        header("Location: index.php?error=".urlencode('Akses ditolak: Anda tidak memiliki izin'));
        exit;
    }
}
function require_admin(): void { require_role('Admin', 'login.php'); }
function require_jurnalis(): void { require_role(['Jurnalis','Admin'], 'login.php'); }
// Note: Jurnalis pages allow Admin as superset, but Admin dashboard is exclusive to Admin
