<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../api/auth.php';
require_once __DIR__ . '/../api/db.php';
require_once __DIR__ . '/../api/repositories/MediaRepository.php';
require_once __DIR__ . '/../api/repositories/TagRepository.php';
require_once __DIR__ . '/../api/repositories/RecommenderRepository.php';
require_once __DIR__ . '/../api/repositories/ListRepository.php';

header('Content-Type: application/json');

$media       = new MediaRepository();
$tags        = new TagRepository();
$recommenders = new RecommenderRepository();
$lists       = new ListRepository();

// Get the request path and method
$path   = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$method = $_SERVER['REQUEST_METHOD'];

// Require auth for all requests
require_auth();

// Route requests
switch ($path) {

    case 'media':
        if ($method === 'GET') {
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
        if ($method === 'GET') {
            echo json_encode($lists->getAll());
        } elseif ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($lists->create($data));
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
        break;
}