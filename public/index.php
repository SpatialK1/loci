<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../api/auth.php';
require_once __DIR__ . '/../api/db.php';
require_once __DIR__ . '/../api/repositories/BaseRepository.php';
require_once __DIR__ . '/../api/repositories/MediaRepository.php';
require_once __DIR__ . '/../api/repositories/TagRepository.php';
require_once __DIR__ . '/../api/repositories/RecommenderRepository.php';
require_once __DIR__ . '/../api/repositories/ListRepository.php';

header('Content-Type: application/json');

$path   = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$method = $_SERVER['REQUEST_METHOD'];

require_auth();

$segments    = explode('/', $path);
$resource    = $segments[0] ?? '';
$id          = isset($segments[1]) ? (int)$segments[1] : null;
$subresource = $segments[2] ?? null;
$subid       = isset($segments[3]) ? (int)$segments[3] : null;

$media        = new MediaRepository();
$tags         = new TagRepository();
$recommenders = new RecommenderRepository();
$lists        = new ListRepository();

switch ($resource) {

    case 'media':
        if ($method === 'GET' && $id) {
            echo json_encode($media->findById($id));
        } elseif ($method === 'GET') {
            echo json_encode($media->getAll($_GET));
        } elseif ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
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
        }
        break;

    case 'recommenders':
        if ($method === 'GET') {
            echo json_encode($recommenders->getAll());
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

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
        break;
}