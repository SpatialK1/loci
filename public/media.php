<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    header('Location: login.php');
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
    <title><?= t('app_name') ?></title>
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime(__DIR__ . '/css/style.css') ?>">
    <link rel="stylesheet" href="css/theme-<?= htmlspecialchars($siteSettings['theme'] ?? 'light') ?>.css" id="theme-stylesheet">
</head>
<body>
    <header>
        <h1 id="site-title"><?= t('app_name') ?></h1>
        <nav>
            <a href="media.php"><?= t('nav_media') ?></a>
            <a href="lists.php"><?= t('nav_lists') ?></a>
            <a href="settings.php"><?= t('nav_settings') ?></a>
            <a href="import.php"><?= t('nav_import') ?></a>
            <button id="logout-btn"><?= t('nav_logout') ?></button>
        </nav>
    </header>

    <main>
        <div id="toolbar">
            <div id="filters">
                <select id="filter-type">
                    <option value=""><?= t('filter_all_types') ?></option>
                    <option value="url"><?= t('type_url') ?></option>
                    <option value="book"><?= t('type_book') ?></option>
                    <option value="movie"><?= t('type_movie') ?></option>
                    <option value="podcast"><?= t('type_podcast') ?></option>
                </select>

                <select id="filter-status">
                    <option value=""><?= t('filter_all_status') ?></option>
                    <option value="queue"><?= t('status_queue') ?></option>
                    <option value="consumed"><?= t('status_consumed') ?></option>
                </select>

                <select id="filter-recommender">
                    <option value=""><?= t('filter_all_recommenders') ?></option>
                </select>

                <input type="text" id="filter-tag" placeholder="<?= t('filter_by_tag') ?>">
            </div>

            <div id="sort-controls">
                <select id="sort-by">
                    <option value="created_at"><?= t('sort_date_added') ?></option>
                    <option value="title"><?= t('sort_title') ?></option>
                    <option value="type"><?= t('sort_type') ?></option>
                    <option value="status"><?= t('sort_status') ?></option>
                    <option value="recommender"><?= t('sort_recommender') ?></option>
                    <option value="show_name"><?= t('sort_show_name') ?></option>
                </select>

                <select id="sort-dir">
                    <option value="DESC"><?= t('sort_newest') ?></option>
                    <option value="ASC"><?= t('sort_oldest') ?></option>
                </select>
            </div>

            <div id="view-controls">
                <button id="view-list"><?= t('view_list') ?></button>
                <button id="view-card"><?= t('view_card') ?></button>
                <button id="add-media"><?= t('media_add') ?></button>
            </div>
        </div>

        <div id="media-container">
            <div id="media-list" class="view-list"></div>
        </div>
    </main>

    <div id="modal-overlay" class="hidden">
        <div id="modal">
            <button id="modal-close"><?= t('close') ?></button>
            <div id="modal-content"></div>
        </div>
    </div>

    <script>
    const Lang = <?= json_encode(I18n::getAllStrings()) ?>;
</script>

    <script src="js/api.js?v=<?= filemtime(__DIR__ . '/js/api.js') ?>"></script>
    <script src="js/media.js?v=<?= filemtime(__DIR__ . '/js/media.js') ?>"></script>
</body>
</html>