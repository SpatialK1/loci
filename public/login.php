<?php
session_start();
require_once __DIR__ . '/../config.php';

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {
    header('Location: media.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loci — Login</title>
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime(__DIR__ . '/css/style.css') ?>">
</head>
<body>
    <main id="login-page">
        <h1>Loci</h1>
        <form id="login-form">
            <label>Username
                <input type="text" name="username" autocomplete="username" required>
            </label>
            <label>Password
                <input type="password" name="password" autocomplete="current-password" required>
            </label>
            <p id="login-error" class="hidden"></p>
            <button type="submit">Log In</button>
        </form>
    </main>
    <script src="js/login.js?v=<?= filemtime(__DIR__ . '/js/login.js') ?>"></script>
</body>
</html>