<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use BlogForge\Repositories\PostRepository;

handle_cors();

$repo = new PostRepository();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Support method overriding via header or query/body _method
if ($method === 'POST') {
    $override = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? $_POST['_method'] ?? null;
    if ($override && in_array(strtoupper($override), ['PUT', 'PATCH', 'DELETE'], true)) {
        $method = strtoupper($override);
    }
}

// ----------------------------------------------------
// GET: Read posts or single post
// ----------------------------------------------------
if ($method === 'GET') {
    // 1. Single post by ID or Slug
    $id = isset($_GET['id']) && is_string($_GET['id']) ? trim($_GET['id']) : '';
    $slug = isset($_GET['slug']) && is_string($_GET['slug']) ? trim($_GET['slug']) : '';
    $key = $id !== '' ? $id : $slug;

    if ($key !== '') {
        $post = find_post($key);
        if (!$post) {
            json_response(['error' => 'Story not found or private.'], 404);
        }
        json_response(['post' => $post]);
    }

    // 2. Author's own posts
    if (isset($_GET['author']) && is_string($_GET['author']) && $_GET['author'] === 'me') {
        require_login();
        $user = current_user();
        $limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $posts = $repo->forAuthor((string)$user['id'], $limit);
        json_response(['posts' => $posts]);
    }

    // 3. Public stories list with optional category, search keyword, and limit
    $limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $category = isset($_GET['category']) && is_string($_GET['category']) ? trim($_GET['category']) : null;
    $search = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : null;

    $posts = public_posts($limit, $category, $search);
    json_response(['posts' => $posts]);
}

// ----------------------------------------------------
// All write operations require authentication & CSRF
// ----------------------------------------------------
require_login();
verify_csrf();
$user = current_user();

// ----------------------------------------------------
// POST: Create a new post
// ----------------------------------------------------
if ($method === 'POST') {
    $d = json_input();
    if (empty($d)) {
        $d = $_POST;
    }

    // Check if client requested delete via POST payload
    if (($d['action'] ?? '') === 'delete' || ($d['_method'] ?? '') === 'DELETE') {
        $id = trim((string)($d['id'] ?? ''));
        try {
            $repo->delete($id, $user);
            json_response(['ok' => true, 'message' => 'Post deleted successfully.']);
        } catch (\Throwable $e) {
            json_response(['error' => $e->getMessage()], 422);
        }
    }

    // Check if client requested update via POST payload with id
    if (!empty($d['id']) && (($d['action'] ?? '') === 'update' || ($d['_method'] ?? '') === 'PUT')) {
        $id = trim((string)$d['id']);
        try {
            $repo->update($id, $d, $user);
            $post = $repo->findById($id);
            json_response([
                'ok' => true,
                'id' => $id,
                'slug' => (string)($post['slug'] ?? $id),
                'message' => 'Story updated successfully.',
            ]);
        } catch (\Throwable $e) {
            json_response(['error' => $e->getMessage()], 422);
        }
    }

    // Standard Create
    try {
        $id = $repo->create($d, $user);
        $post = $repo->findById($id);
        json_response([
            'ok' => true,
            'id' => $id,
            'slug' => (string)($post['slug'] ?? $id),
            'message' => 'Story created successfully.',
        ], 201);
    } catch (\Throwable $e) {
        json_response(['error' => $e->getMessage()], 422);
    }
}

// ----------------------------------------------------
// PUT / PATCH: Update an existing post
// ----------------------------------------------------
if ($method === 'PUT' || $method === 'PATCH') {
    $d = json_input();
    $id = trim((string)($d['id'] ?? $_GET['id'] ?? ''));

    if ($id === '') {
        json_response(['error' => 'Missing story ID for update.'], 400);
    }

    try {
        $repo->update($id, $d, $user);
        $post = $repo->findById($id);
        json_response([
            'ok' => true,
            'id' => $id,
            'slug' => (string)($post['slug'] ?? $id),
            'message' => 'Story updated successfully.',
        ]);
    } catch (\Throwable $e) {
        json_response(['error' => $e->getMessage()], 422);
    }
}

// ----------------------------------------------------
// DELETE: Remove a post
// ----------------------------------------------------
if ($method === 'DELETE') {
    $d = json_input();
    $id = trim((string)($d['id'] ?? $_GET['id'] ?? ''));

    if ($id === '') {
        json_response(['error' => 'Missing story ID for deletion.'], 400);
    }

    try {
        $repo->delete($id, $user);
        json_response(['ok' => true, 'message' => 'Story deleted successfully.']);
    } catch (\Throwable $e) {
        json_response(['error' => $e->getMessage()], 422);
    }
}

json_response(['error' => 'Method not allowed.'], 405);
