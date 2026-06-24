<?php
define('AUTH_USER', 'your_actual_username');
define('AUTH_PASS', 'your_actual_hash');

function verify_credentials(string $username, string $password): bool {
    return $username === AUTH_USER && password_verify($password, AUTH_PASS);
}

function require_auth(): void {
    if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
        try {
            $setting = DB::queryFirstRow("SELECT `value` FROM settings WHERE `key` = 'site_public'");
            if ($setting && $setting['value'] === 'true' && $_SERVER['REQUEST_METHOD'] === 'GET') {
                return;
            }
        } catch (\Exception $e) {
            // fall through to auth required
        }
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

function current_user_id(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function is_admin(): bool {
    return ($_SESSION['user_role'] ?? '') === 'admin';
}