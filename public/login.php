<?php
session_start();
require_once __DIR__ . '/../config.php';

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {
    header('Location: media.php');
    exit;
}

require_once __DIR__ . '/../api/helpers/i18n.php';
require_once __DIR__ . '/../api/db.php';
require_once __DIR__ . '/../api/repositories/BaseRepository.php';
require_once __DIR__ . '/../api/repositories/SettingsRepository.php';

$settingsRepo = new SettingsRepository();
$siteSettings = $settingsRepo->getAll();
$langOverride = $siteSettings['language'] !== 'auto' ? $siteSettings['language'] : null;
I18n::init($langOverride);

$dir = I18n::isRTL() ? 'rtl' : 'ltr';
?>
<!DOCTYPE html>
<html lang="<?= I18n::getActiveLanguage() ?>" dir="<?= $dir ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('app_name') ?> — <?= t('login_title') ?></title>
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime(__DIR__ . '/css/style.css') ?>">
</head>
<body>
    <main id="login-page">
        <h1><?= t('app_name') ?></h1>
        <form id="login-form">
            <label><?= t('login_username') ?>
                <input type="text" name="username" autocomplete="username" required>
            </label>
            <label><?= t('login_password') ?>
                <input type="password" name="password" autocomplete="current-password" required>
            </label>
            <p id="login-error" class="hidden"></p>
            <button type="submit"><?= t('login_submit') ?></button>
        </form>
    </main>
    <script>
        const Lang = <?= json_encode([
            'login_error'         => t('login_error'),
            'login_generic_error' => t('login_generic_error'),
        ]) ?>;
    </script>
    <script src="js/login.js?v=<?= filemtime(__DIR__ . '/js/login.js') ?>"></script>
</body>
</html>