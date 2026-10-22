<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use MongoDB\BSON\UTCDateTime;

handle_cors();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed.'], 405);
}

require_login();
verify_csrf();

$d = json_input();
if (empty($d)) {
    $d = $_POST;
}

$postId = trim((string)($d['post_id'] ?? ''));
if ($postId === '') {
    json_response(['error' => 'Missing story ID.'], 400);
}

$db = db();
if (!$db) {
    // In demo / fallback mode, simulate toggle gracefully
    $simulatedLiked = !empty($_SESSION['demo_liked_' . $postId]);
    $simulatedLiked = !$simulatedLiked;
    $_SESSION['demo_liked_' . $postId] = $simulatedLiked;
    json_response(['liked' => $simulatedLiked, 'likes' => $simulatedLiked ? 1 : 0]);
}

$user = current_user();
$existing = $db->reactions->findOne([
    'post_id' => $postId,
    'user_id' => (string)$user['id'],
    'type' => 'like',
]);

$liked = !$existing;

if ($existing) {
    $db->reactions->deleteOne(['_id' => $existing['_id']]);
} else {
    $db->reactions->insertOne([
        'post_id' => $postId,
        'user_id' => (string)$user['id'],
        'type' => 'like',
        'created_at' => new UTCDateTime(),
    ]);
}

$likes = 0;
$oid = oid($postId);
if ($oid) {
    $inc = $liked ? 1 : -1;
    $db->posts->updateOne(['_id' => $oid], ['$inc' => ['likes' => $inc]]);
    
    // Ensure likes never fall below zero
    $db->posts->updateOne(['_id' => $oid, 'likes' => ['$lt' => 0]], ['$set' => ['likes' => 0]]);
    
    $post = $db->posts->findOne(['_id' => $oid]);
    $likes = max(0, (int)($post['likes'] ?? 0));
} else {
    // For non-ObjectId demo or string IDs
    $likes = $db->reactions->countDocuments(['post_id' => $postId, 'type' => 'like']);
}

json_response(['liked' => $liked, 'likes' => $likes]);
