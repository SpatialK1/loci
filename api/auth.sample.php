<?php
// Copy this file to auth.php and fill in your values
// Generate a bcrypt hash for your password by running:
// php -r "echo password_hash('your_chosen_password', PASSWORD_DEFAULT);"

define('AUTH_USER', 'your_username');
define('AUTH_PASS', 'your_bcrypt_hash');

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