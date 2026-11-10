<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

handle_cors();
require_login();
verify_csrf();
check_rate_limit('image_upload', 20, 60);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed.'], 405);
}

$input = json_input();
$imageData = $input['image'] ?? $_POST['image'] ?? null;

if (!$imageData && !empty($_FILES['file']['tmp_name'])) {
    $file = $_FILES['file'];
    if ($file['size'] > 2 * 1024 * 1024) {
        json_response(['error' => 'File size exceeds 2MB limit.'], 422);
    }
    $imageData = 'data:' . ($file['type'] ?: 'image/jpeg') . ';base64,' . base64_encode(file_get_contents($file['tmp_name']));
}

if (!$imageData || !is_string($imageData)) {
    json_response(['error' => 'No image data provided.'], 422);
}

// Validate base64 payload size (max ~500KB encoded = ~375KB binary image)
if (strlen($imageData) > 500 * 1024) {
    json_response(['error' => 'Image payload exceeds maximum allowed size (500KB compressed). Please reduce image dimensions or quality.'], 422);
}

// Validate data URI format to prevent non-image uploads and script injection
if (str_starts_with($imageData, 'data:') && !preg_match('~^data:image/(webp|jpeg|jpg|png|gif);base64,[a-zA-Z0-9/+=]+$~i', $imageData)) {
    json_response(['error' => 'Invalid image format. Only WebP, JPEG, PNG, and GIF data URIs are accepted.'], 422);
}

// 1. Try uploading to Cloudinary if configured
$cloudinaryUrl = upload_to_cloudinary($imageData);
if ($cloudinaryUrl) {
    json_response([
        'ok' => true,
        'url' => $cloudinaryUrl,
        'storage' => 'cloudinary',
    ]);
}

// 2. Otherwise return the optimized base64 data URI (ready for database storage without external dependencies)
json_response([
    'ok' => true,
    'url' => $imageData,
    'storage' => 'base64',
]);
