<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

handle_cors();
require_login();
verify_csrf();
check_rate_limit('image_upload', 30, 60);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed.'], 405);
}

$user = current_user();
$userId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($user['id'] ?? 'user'));

$input = json_input();
$imageData = $input['image'] ?? $_POST['image'] ?? null;
$type = (string)($input['type'] ?? $_POST['type'] ?? 'general');

// Check if raw multipart file upload was provided
if (!$imageData && !empty($_FILES['file']['tmp_name'])) {
    $file = $_FILES['file'];
    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        json_response(['error' => 'File size exceeds 2MB limit.'], 422);
    }
    if (!empty($file['error']) || !is_uploaded_file($file['tmp_name'])) {
        json_response(['error' => 'Failed to upload file. Please try again.'], 422);
    }

    $mime = mime_content_type($file['tmp_name']) ?: ($file['type'] ?? 'image/jpeg');
    $imageData = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file['tmp_name']));
}

if (!$imageData || !is_string($imageData)) {
    json_response(['error' => 'No image data provided.'], 422);
}

// Extract base64 payload and validate format
if (!preg_match('~^data:image/(webp|jpeg|jpg|png|gif);base64,([a-zA-Z0-9/+=]+)$~i', $imageData, $matches)) {
    json_response(['error' => 'Invalid image format. Only WebP, JPEG, PNG, and GIF images under 2MB are accepted.'], 422);
}

$format = strtolower($matches[1]);
if ($format === 'jpeg') {
    $format = 'jpg';
}
$rawBinary = base64_decode($matches[2], true);

if ($rawBinary === false) {
    json_response(['error' => 'Failed to decode image data.'], 422);
}

if (strlen($rawBinary) > 2 * 1024 * 1024) {
    json_response(['error' => 'Image size exceeds 2MB limit.'], 422);
}

// Validate magic bytes to verify genuine image content
$header12 = substr($rawBinary, 0, 12);
$isValidImage = false;
if (str_starts_with($header12, "\xFF\xD8\xFF")) {
    $isValidImage = true; // JPEG
} elseif (str_starts_with($header12, "\x89PNG\r\n\x1a\n")) {
    $isValidImage = true; // PNG
} elseif (str_starts_with($header12, "GIF87a") || str_starts_with($header12, "GIF89a")) {
    $isValidImage = true; // GIF
} elseif (str_starts_with($header12, "RIFF") && substr($header12, 8, 4) === "WEBP") {
    $isValidImage = true; // WebP
}

if (!$isValidImage) {
    json_response(['error' => 'The uploaded file is not a valid image format.'], 422);
}

// 1. Try Cloudinary first if configured
$cloudinaryFolder = ($type === 'avatar') ? 'blogforge/avatars' : 'blogforge';
$cloudinaryUrl = upload_to_cloudinary($imageData, $cloudinaryFolder);
if ($cloudinaryUrl) {
    json_response([
        'ok' => true,
        'url' => $cloudinaryUrl,
        'storage' => 'cloudinary',
    ]);
}

// 2. High-speed local persistent file storage
$subDir = ($type === 'avatar') ? 'avatars' : 'covers';
$uploadDir = dirname(__DIR__) . '/uploads/' . $subDir;
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$ext = ($format === 'jpg') ? 'jpg' : $format;
$prefix = ($type === 'avatar') ? 'avatar_' . $userId . '_' : 'cover_';
$filename = $prefix . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$destPath = $uploadDir . '/' . $filename;

if (file_put_contents($destPath, $rawBinary) === false) {
    json_response(['error' => 'Failed to save image file on server.'], 500);
}

$webUrl = '/uploads/' . $subDir . '/' . $filename;

json_response([
    'ok' => true,
    'url' => $webUrl,
    'storage' => 'local',
]);
