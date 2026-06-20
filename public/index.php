<?php

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 86400);
session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../api/auth.php';
require_once __DIR__ . '/../api/db.php';
require_once __DIR__ . '/../api/repositories/BaseRepository.php';
require_once __DIR__ . '/../api/repositories/MediaRepository.php';
require_once __DIR__ . '/../api/repositories/TagRepository.php';
require_once __DIR__ . '/../api/repositories/RecommenderRepository.php';
require_once __DIR__ . '/../api/repositories/ListRepository.php';
require_once __DIR__ . '/../api/repositories/SettingsRepository.php';

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

header('Content-Type: application/json');

$path   = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$method = $_SERVER['REQUEST_METHOD'];

$segments    = explode('/', $path);
$resource    = $segments[0] ?? '';
$id          = isset($segments[1]) ? (int)$segments[1] : null;
$subresource = $segments[2] ?? null;
$subid       = isset($segments[3]) ? (int)$segments[3] : null;

// Public share token route — no auth required
if ($resource === 'share' && !empty($segments[1])) {
    $lists = new ListRepository();
    $list  = $lists->findByToken($segments[1]);
    if (!$list) {
        http_response_code(404);
        echo json_encode(['error' => 'List not found']);
    } else {
        echo json_encode($list);
    }
    exit;
}

// Login route — no auth required
if ($resource === 'login' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (verify_credentials($data['username'] ?? '', $data['password'] ?? '')) {
        $_SESSION['authenticated'] = true;
        echo json_encode(['success' => true]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid username or password']);
    }
    exit;
}

// Logout route
if ($resource === 'logout' && $method === 'POST') {
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

// Check session auth
require_auth();

$media        = new MediaRepository();
$tags         = new TagRepository();
$recommenders = new RecommenderRepository();
$lists        = new ListRepository();
$settings     = new SettingsRepository();

switch ($resource) {

    case 'media':
        if ($method === 'GET' && $id) {
            echo json_encode($media->findById($id));
        } elseif ($method === 'GET') {
            echo json_encode($media->getAll($_GET));
        } elseif ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
        
        // Check for duplicates unless force flag is set
        if (empty($data['force'])) {
            require_once __DIR__ . '/../api/repositories/DuplicateDetector.php';
            $settings_repo = new SettingsRepository();
            $site_settings = $settings_repo->getAll();
            $lang = $site_settings['language'] !== 'auto' ? $site_settings['language'] : 'en';
            DuplicateDetector::init($lang);
            $existing = $media->getAll([]);
            $duplicates = DuplicateDetector::findDuplicates($data, $existing);
            if (!empty($duplicates)) {
                echo json_encode([
                    'status'     => 'duplicates_found',
                    'duplicates' => $duplicates,
                    'incoming'   => $data,
                ]);
                break;
            }
        }
    
        if (!empty($data['recommender'])) {
            $data['recommender_id'] = $recommenders->findOrCreate($data['recommender']);
        }
        $item = $media->create($data);
        if (!empty($data['tags'])) {
            $tags->syncTagsForMedia($item['id'], $data['tags']);
            $item = $media->findById($item['id']);
        }
        echo json_encode($item);
        } elseif ($method === 'PUT' && $id) {
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($media->update($id, $data));
        } elseif ($method === 'DELETE' && $id) {
            echo json_encode(['success' => $media->delete($id)]);
        }
        break;

    case 'tags':
        if ($method === 'GET') {
            echo json_encode($tags->getAll());
        } elseif ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($tags->create($data['name']));
        } elseif ($method === 'PUT' && $id) {
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($tags->update($id, $data['name']));
        } elseif ($method === 'DELETE' && $id) {
            echo json_encode(['success' => $tags->delete($id)]);
        }
        break;

    case 'recommenders':
        if ($method === 'GET' && $id) {
            echo json_encode($recommenders->findById($id));
        } elseif ($method === 'GET') {
            echo json_encode($recommenders->getAll());
        } elseif ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($recommenders->create($data['name']));
        } elseif ($method === 'PUT' && $id) {
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($recommenders->update($id, $data['name']));
        } elseif ($method === 'DELETE' && $id) {
            echo json_encode(['success' => $recommenders->delete($id)]);
        }
        break;

    case 'lists':
        if ($method === 'GET' && $id) {
            echo json_encode($lists->findById($id));
        } elseif ($method === 'GET') {
            echo json_encode($lists->getAll());
        } elseif ($method === 'POST' && !$id) {
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($lists->create($data));
        } elseif ($method === 'POST' && $id && $subresource === 'media') {
            $data = json_decode(file_get_contents('php://input'), true);
            $lists->addMedia($id, (int)$data['media_id']);
            echo json_encode($lists->findById($id));
        } elseif ($method === 'PUT' && $id) {
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($lists->update($id, $data));
        } elseif ($method === 'DELETE' && $id && $subresource === 'media' && $subid) {
            $lists->removeMedia($id, $subid);
            echo json_encode($lists->findById($id));
        } elseif ($method === 'DELETE' && $id) {
            echo json_encode(['success' => $lists->delete($id)]);
        }
        break;

    case 'settings':
        if ($method === 'GET') {
            echo json_encode($settings->getAll());
        } elseif ($method === 'PUT') {
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($settings->setMany($data));
        }
        break;    

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
        break;
}