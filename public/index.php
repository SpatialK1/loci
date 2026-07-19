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
require_once __DIR__ . '/../api/repositories/UserRepository.php';
require_once __DIR__ . '/../api/repositories/InvitationRepository.php';
require_once __DIR__ . '/../api/repositories/RecommendationRepository.php';

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
    $data     = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    $userRepo = new UserRepository();
    $user     = $userRepo->findByUsername($username);

    if (!$user || !$user['is_active'] || !password_verify($password, $user['password_hash'])) {
        if (verify_credentials($username, $password)) {
            $_SESSION['authenticated'] = true;
            $_SESSION['user_id']       = null;
            $_SESSION['user_role']     = 'admin';
            echo json_encode(['success' => true]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid username or password']);
        }
        exit;
    }

    $_SESSION['authenticated'] = true;
    $_SESSION['user_id']       = $user['id'];
    $_SESSION['user_role']     = $user['role'];
    echo json_encode(['success' => true, 'user' => $userRepo->safeView($user)]);
    exit;
}

// Register route — no auth required
if ($resource === 'register' && $method === 'POST') {
    $data  = json_decode(file_get_contents('php://input'), true);
    $token = $data['token'] ?? '';

    $invitationRepo = new InvitationRepository();
    $invitation     = $invitationRepo->findByToken($token);

    if (!$invitation) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid or expired invitation']);
        exit;
    }

    $userRepo = new UserRepository();

    if ($userRepo->findByUsername($data['username'] ?? '')) {
        http_response_code(400);
        echo json_encode(['error' => 'Username already taken']);
        exit;
    }

    $existingUsers = $userRepo->getAll();
    $role          = empty($existingUsers) ? 'admin' : 'member';

    $user = $userRepo->create([
        'username' => $data['username'],
        'email'    => $invitation['email'],
        'password' => $data['password'],
        'role'     => $role,
    ]);

    $invitationRepo->accept($token);

    $_SESSION['authenticated'] = true;
    $_SESSION['user_id']       = $user['id'];
    $_SESSION['user_role']     = $user['role'];

    echo json_encode(['success' => true, 'user' => $userRepo->safeView($user)]);
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

$media           = new MediaRepository();
$tags            = new TagRepository();
$recommenders    = new RecommenderRepository();
$lists           = new ListRepository();
$settings        = new SettingsRepository();
$users           = new UserRepository();
$invitations     = new InvitationRepository();
$recommendations = new RecommendationRepository();

$userId = current_user_id();

switch ($resource) {

    case 'media':
        if ($method === 'GET' && $id) {
            echo json_encode($media->findById($id, $userId));
        } elseif ($method === 'GET') {
            echo json_encode($media->getAll($_GET, $userId));
        } elseif ($method === 'POST') {
            $data            = json_decode(file_get_contents('php://input'), true);
            $data['user_id'] = $userId;

            if (empty($data['force'])) {
                require_once __DIR__ . '/../api/repositories/DuplicateDetector.php';
                $settings_repo = new SettingsRepository();
                $site_settings = $settings_repo->getAll();
                $lang          = $site_settings['language'] !== 'auto' ? $site_settings['language'] : 'en';
                DuplicateDetector::init($lang);
                $existing   = $media->getAll([], $userId);
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
                $item = $media->findById($item['id'], $userId);
            }
            echo json_encode($item);
        } elseif ($method === 'PUT' && $id) {
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($media->update($id, $userId, $data));
        } elseif ($method === 'DELETE' && $id) {
            echo json_encode(['success' => $media->delete($id, $userId)]);
        }
        break;

    case 'tags':
        if ($method === 'GET') {
            echo json_encode($tags->getAll($userId));
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
            echo json_encode($lists->findById($id, $userId));
        } elseif ($method === 'GET') {
            echo json_encode($lists->getAll($userId));
        } elseif ($method === 'POST' && !$id) {
            $data            = json_decode(file_get_contents('php://input'), true);
            $data['user_id'] = $userId;
            echo json_encode($lists->create($data));
        } elseif ($method === 'POST' && $id && $subresource === 'media') {
            $data = json_decode(file_get_contents('php://input'), true);
            $lists->addMedia($id, (int)$data['media_id'], $userId);
            echo json_encode($lists->findById($id, $userId));
        } elseif ($method === 'PUT' && $id) {
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($lists->update($id, $userId, $data));
        } elseif ($method === 'DELETE' && $id && $subresource === 'media' && $subid) {
            $lists->removeMedia($id, $subid, $userId);
            echo json_encode($lists->findById($id, $userId));
        } elseif ($method === 'DELETE' && $id) {
            echo json_encode(['success' => $lists->delete($id, $userId)]);
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

    case 'users':
        if ($method === 'GET') {
            echo json_encode(array_map([$users, 'safeView'], $users->getAll()));
        } elseif ($method === 'PUT' && $id) {
            if (!is_admin() && $userId !== $id) {
                http_response_code(403);
                echo json_encode(['error' => 'Forbidden']);
                break;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($users->safeView($users->update($id, $data)));
        } elseif ($method === 'DELETE' && $id) {
            if (!is_admin()) {
                http_response_code(403);
                echo json_encode(['error' => 'Forbidden']);
                break;
            }
            echo json_encode(['success' => $users->delete($id)]);
        }
        break;

    case 'invitations':
        if ($method === 'GET') {
            if (!is_admin()) {
                http_response_code(403);
                echo json_encode(['error' => 'Forbidden']);
                break;
            }
            echo json_encode($invitations->getAll());
        } elseif ($method === 'POST') {
            if (!is_admin()) {
                http_response_code(403);
                echo json_encode(['error' => 'Forbidden']);
                break;
            }
            $data       = json_decode(file_get_contents('php://input'), true);
            $invitation = $invitations->create($data['email'], $userId);
            $invitations->sendInvitationEmail(
                $invitation['email'],
                $invitation['token'],
                $_SESSION['username'] ?? 'Someone'
            );
            echo json_encode($invitation);
        } elseif ($method === 'DELETE' && $id) {
            if (!is_admin()) {
                http_response_code(403);
                echo json_encode(['error' => 'Forbidden']);
                break;
            }
            echo json_encode(['success' => $invitations->delete($id)]);
        }
        break;

    case 'recommendations':
        if ($method === 'GET') {
            echo json_encode($recommendations->getPendingForUser($userId));
        } elseif ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($recommendations->create(
                $userId,
                $data['to_user_ids'],
                (int)$data['media_id']
            ));
        }
        break;

    case 'recommendations/accept':
        if ($method === 'POST' && $id) {
            echo json_encode($recommendations->accept($id, $userId));
        }
        break;

    case 'recommendations/decline':
        if ($method === 'POST' && $id) {
            echo json_encode(['success' => $recommendations->decline($id, $userId)]);
        }
        break;

    case 'recommendations/sent':
        if ($method === 'GET') {
            echo json_encode($recommendations->getSentByUser($userId));
        }
        break;

    case 'recipients':
        if ($method === 'GET') {
            echo json_encode($recommendations->getEligibleRecipients($userId));
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
        break;
}