<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use BlogForge\Repositories\CommentRepository;

handle_cors();

$repo = new CommentRepository();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
    $override = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? $_POST['_method'] ?? null;
    if ($override && strtoupper($override) === 'DELETE') {
        $method = 'DELETE';
    }
}

// ----------------------------------------------------
// GET: Fetch comments for a post
// ----------------------------------------------------
if ($method === 'GET') {
    $postId = isset($_GET['post_id']) && is_string($_GET['post_id']) ? trim($_GET['post_id']) : '';
    if ($postId === '') {
        json_response(['comments' => []]);
    }

    $comments = $repo->forPost($postId);
    json_response(['comments' => $comments]);
}

// ----------------------------------------------------
// Write operations require authentication and CSRF
// ----------------------------------------------------
require_login();
verify_csrf();
$user = current_user();

// ----------------------------------------------------
// POST: Add a new comment
// ----------------------------------------------------
if ($method === 'POST') {
    $d = json_input();
    if (empty($d)) {
        $d = $_POST;
    }

    if (($d['action'] ?? '') === 'delete') {
        $id = trim((string)($d['id'] ?? ''));
        try {
            $repo->delete($id, $user);
            json_response(['ok' => true, 'message' => 'Comment deleted.']);
        } catch (\Throwable $e) {
            json_response(['error' => $e->getMessage()], 422);
        }
    }

    $postId = trim((string)($d['post_id'] ?? ''));
    $body = trim((string)($d['body'] ?? ''));

    if ($postId === '') {
        json_response(['error' => 'Missing story ID.'], 400);
    }

    if ($body === '') {
        json_response(['error' => 'Comment content cannot be empty.'], 422);
    }

    try {
        $id = $repo->add($postId, $body, $user);
        json_response([
            'ok' => true,
            'id' => $id,
            'comment' => [
                'id' => $id,
                'post_id' => $postId,
                'body' => $body,
                'author' => [
                    'id' => (string)$user['id'],
                    'name' => (string)$user['name'],
                    'handle' => (string)($user['handle'] ?? ''),
                ],
                'created_at' => time(),
            ],
            'message' => 'Comment published successfully.',
        ], 201);
    } catch (\Throwable $e) {
        json_response(['error' => $e->getMessage()], 422);
    }
}

// ----------------------------------------------------
// DELETE: Remove a comment
// ----------------------------------------------------
if ($method === 'DELETE') {
    $d = json_input();
    $id = trim((string)($d['id'] ?? $_GET['id'] ?? ''));

    if ($id === '') {
        json_response(['error' => 'Missing comment ID.'], 400);
    }

    try {
        $repo->delete($id, $user);
        json_response(['ok' => true, 'message' => 'Comment deleted successfully.']);
    } catch (\Throwable $e) {
        json_response(['error' => $e->getMessage()], 422);
    }
}

json_response(['error' => 'Method not allowed.'], 405);
