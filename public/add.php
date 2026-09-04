<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    $redirect = urlencode($_SERVER['REQUEST_URI']);
    header('Location: login.php?redirect=' . $redirect);
    exit;
}

require_once __DIR__ . '/../api/helpers/i18n.php';
require_once __DIR__ . '/../api/db.php';
require_once __DIR__ . '/../api/repositories/BaseRepository.php';
require_once __DIR__ . '/../api/repositories/SettingsRepository.php';
require_once __DIR__ . '/../api/repositories/UserRepository.php';

$settingsRepo = new SettingsRepository();
$siteSettings = $settingsRepo->getAll();
$langOverride = $siteSettings['language'] !== 'auto' ? $siteSettings['language'] : null;
I18n::init($langOverride);

$userRepo = new UserRepository();
$user     = $userRepo->findById($_SESSION['user_id']);

$prefillUrl   = htmlspecialchars($_GET['url'] ?? '');
$prefillTitle = htmlspecialchars($_GET['title'] ?? '');

$dir = I18n::isRTL() ? 'rtl' : 'ltr';
?>
<!DOCTYPE html>
<html lang="<?= I18n::getActiveLanguage() ?>" dir="<?= $dir ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('media_add_title') ?> — <?= t('app_name') ?></title>
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime(__DIR__ . '/css/style.css') ?>">
    <link rel="stylesheet" href="css/theme-<?= htmlspecialchars($siteSettings['theme'] ?? 'light') ?>.css?v=<?= filemtime(__DIR__ . '/css/theme-' . ($siteSettings['theme'] ?? 'light') . '.css') ?>">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body id="add-page">
    <header>
        <h1><?= t('app_name') ?></h1>
        <a href="media.php"><?= t('nav_media') ?></a>
    </header>

    <main>
        <h2><?= t('media_add_title') ?></h2>
        <form id="add-form">
            <label><?= t('field_type') ?>
                <select name="type" id="type-select">
                    <option value="url" selected><?= t('type_url') ?></option>
                    <option value="book"><?= t('type_book') ?></option>
                    <option value="movie"><?= t('type_movie') ?></option>
                    <option value="podcast"><?= t('type_podcast') ?></option>
                </select>
            </label>
            <label><?= t('field_title') ?>
                <input type="text" name="title" value="<?= $prefillTitle ?>" required>
            </label>
            <label><?= t('field_author') ?>
                <input type="text" name="author">
            </label>
            <label><?= t('field_url') ?>
                <input type="url" name="url" value="<?= $prefillUrl ?>">
            </label>
            <label><?= t('field_notes') ?>
                <textarea name="notes"></textarea>
            </label>
            <label><?= t('field_recommender') ?>
                <input type="text" name="recommender">
            </label>
            <label><?= t('field_tags') ?>
                <input type="text" name="tags" placeholder="<?= t('field_tags_hint') ?>">
            </label>
            <label><?= t('field_status') ?>
                <select name="status">
                    <option value="acquired" selected><?= t('status_acquired') ?></option>
                    <option value="find"><?= t('status_find') ?></option>
                    <option value="consumed"><?= t('status_consumed') ?></option>
                </select>
            </label>
            <label><?= t('field_visibility') ?>
                <select name="visibility">
                    <option value="<?= htmlspecialchars($user['default_visibility'] ?? 'group') ?>" selected>
                        <?= t('visibility_' . ($user['default_visibility'] ?? 'group')) ?>
                    </option>
                    <?php foreach (['private', 'group', 'public'] as $v): ?>
                        <?php if ($v !== ($user['default_visibility'] ?? 'group')): ?>
                            <option value="<?= $v ?>"><?= t('visibility_' . $v) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </label>
            <div id="form-actions">
                <button type="submit"><?= t('add') ?></button>
                <button type="button" id="cancel-btn"><?= t('cancel') ?></button>
            </div>
            <p id="form-message" class="hidden"></p>
        </form>
    </main>

    <script>
        const Lang = <?= json_encode(I18n::getAllStrings()) ?>;
    </script>
    <script src="js/api.js?v=<?= filemtime(__DIR__ . '/js/api.js') ?>"></script>
    <script src="js/add.js?v=<?= filemtime(__DIR__ . '/js/add.js') ?>"></script>
</body>
</html>