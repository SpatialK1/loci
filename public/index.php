<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../api/auth.php';

// Get the request path and method
$path = trim($_SERVER['REQUEST_URI'], '/');
$method = $_SERVER['REQUEST_METHOD'];

// Strip query string from path
if (strpos($path, '?') !== false) {
    $path = substr($path, 0, strpos($path, '?'));
}

// Require auth for all requests
require_auth();

// Route requests
switch ($path) {
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
        break;
}