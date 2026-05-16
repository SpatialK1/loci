<?php
// Copy this file to auth.php and fill in your values
// Generate a bcrypt hash for your password by running:
// php -r "echo password_hash('your_chosen_password', PASSWORD_DEFAULT);"

define('AUTH_USER', 'your_username');
define('AUTH_PASS', 'your_bcrypt_hash');

function require_auth(): void {
    if (SITE_PUBLIC && $_SERVER['REQUEST_METHOD'] === 'GET') {
        return;
    }

    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
        auth_prompt();
    }

    if (
        $_SERVER['PHP_AUTH_USER'] !== AUTH_USER ||
        !password_verify($_SERVER['PHP_AUTH_PW'], AUTH_PASS)
    ) {
        auth_prompt();
    }
}

function auth_prompt(): void {
    header('WWW-Authenticate: Basic realm="Loci"');
    header('HTTP/1.0 401 Unauthorized');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
