<?php
declare(strict_types=1);

namespace BlogForge\Repositories;

use BlogForge\Database;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

final class PostRepository
{
    /**
     * Create a new post.
     */
    public function create(array $data, array $user): string
    {
        $db = Database::connection();
        if (!$db) {
            throw new \RuntimeException('Database is currently offline. Cannot create post.');
        }

        $title = trim((string)($data['title'] ?? ''));
        $body = trim((string)($data['body'] ?? ''));

        if (mb_strlen($title) < 4 || mb_strlen($title) > 200) {
            throw new \RuntimeException('Post title must be between 4 and 200 characters.');
        }

        if (mb_strlen($body) < 20) {
            throw new \RuntimeException('Story body must contain at least 20 characters.');
        }

        $status = in_array(($data['status'] ?? 'draft'), ['draft', 'published'], true)
            ? (string)$data['status']
            : 'draft';

        $rawExcerpt = trim((string)($data['excerpt'] ?? ''));
        $excerpt = $rawExcerpt !== '' ? $rawExcerpt : mb_substr(strip_tags($body), 0, 180);

        $category = trim((string)($data['category'] ?? 'General'));
        if ($category === '') {
            $category = 'General';
        }

        $cover = trim((string)($data['cover'] ?? ''));

        // Sanitize tags
        $tags = [];
        if (!empty($data['tags'])) {
            $rawTags = is_array($data['tags']) ? $data['tags'] : explode(',', (string)$data['tags']);
            $tags = array_values(array_slice(array_filter(array_map('trim', $rawTags)), 0, 8));
        }

        $baseSlug = slugify($title);
        $slug = $baseSlug . '-' . substr(bin2hex(random_bytes(3)), 0, 6);

        $now = new UTCDateTime();
        $doc = [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'body' => $body,
            'category' => $category,
            'tags' => $tags,
            'status' => $status,
            'author' => [
                'id' => (string)$user['id'],
                'name' => (string)$user['name'],
                'handle' => (string)($user['handle'] ?? ''),
            ],
            'cover' => $cover,
            'read_time' => reading_time($body),
            'likes' => 0,
            'comments' => 0,
            'created_at' => $now,
            'updated_at' => $now,
            'published_at' => $status === 'published' ? $now : null,
        ];

        $result = $db->posts->insertOne($doc);
        return (string)$result->getInsertedId();
    }

    /**
     * Update an existing post by ID.
     */
    public function update(string $id, array $data, array $user): bool
    {
        $db = Database::connection();
        if (!$db) {
            throw new \RuntimeException('Database is currently offline. Cannot update post.');
        }

        $oid = oid($id);
        if (!$oid) {
            throw new \InvalidArgumentException('Invalid post ID provided.');
        }

        $post = $db->posts->findOne(['_id' => $oid]);
        if (!$post) {
            throw new \RuntimeException('Post not found.');
        }

        $authorId = (string)($post['author']['id'] ?? '');
        $isAdmin = ($user['role'] ?? '') === 'admin';

        if (!$isAdmin && $authorId !== (string)$user['id']) {
            throw new \RuntimeException('You do not have permission to update this post.');
        }

        $set = [];

        if (isset($data['title'])) {
            $title = trim((string)$data['title']);
            if (mb_strlen($title) < 4 || mb_strlen($title) > 200) {
                throw new \RuntimeException('Post title must be between 4 and 200 characters.');
            }
            $set['title'] = $title;
        }

        if (isset($data['body'])) {
            $body = trim((string)$data['body']);
            if (mb_strlen($body) < 20) {
                throw new \RuntimeException('Story body must contain at least 20 characters.');
            }
            $set['body'] = $body;
            $set['read_time'] = reading_time($body);
        }

        if (isset($data['excerpt'])) {
            $set['excerpt'] = trim((string)$data['excerpt']);
        } elseif (isset($set['body']) && empty($post['excerpt'])) {
            $set['excerpt'] = mb_substr(strip_tags($set['body']), 0, 180);
        }

        if (isset($data['category'])) {
            $cat = trim((string)$data['category']);
            $set['category'] = $cat !== '' ? $cat : 'General';
        }

        if (isset($data['cover'])) {
            $set['cover'] = trim((string)$data['cover']);
        }

        if (isset($data['tags'])) {
            $rawTags = is_array($data['tags']) ? $data['tags'] : explode(',', (string)$data['tags']);
            $set['tags'] = array_values(array_slice(array_filter(array_map('trim', $rawTags)), 0, 8));
        }

        if (isset($data['status']) && in_array($data['status'], ['draft', 'published'], true)) {
            $set['status'] = $data['status'];
            if ($data['status'] === 'published' && empty($post['published_at'])) {
                $set['published_at'] = new UTCDateTime();
            }
        }

        $set['updated_at'] = new UTCDateTime();

        $updateResult = $db->posts->updateOne(['_id' => $oid], ['$set' => $set]);
        return $updateResult->getMatchedCount() > 0;
    }

    /**
     * Delete a post by ID and remove related comments and reactions.
     */
    public function delete(string $id, array $user): bool
    {
        $db = Database::connection();
        if (!$db) {
            throw new \RuntimeException('Database is currently offline. Cannot delete post.');
        }

        $oid = oid($id);
        if (!$oid) {
            throw new \InvalidArgumentException('Invalid post ID provided.');
        }

        $post = $db->posts->findOne(['_id' => $oid]);
        if (!$post) {
            throw new \RuntimeException('Post not found.');
        }

        $authorId = (string)($post['author']['id'] ?? '');
        $isAdmin = ($user['role'] ?? '') === 'admin';

        if (!$isAdmin && $authorId !== (string)$user['id']) {
            throw new \RuntimeException('You do not have permission to delete this post.');
        }

        // Delete post
        $db->posts->deleteOne(['_id' => $oid]);

        // Clean up associated comments and reactions
        $db->comments->deleteMany(['post_id' => $id]);
        $db->reactions->deleteMany(['post_id' => $id]);

        return true;
    }

    /**
     * Find single post by string ID.
     */
    public function findById(string $id): ?array
    {
        $db = Database::connection();
        if (!$db) {
            return null;
        }

        $oid = oid($id);
        if (!$oid) {
            return null;
        }

        $post = $db->posts->findOne(['_id' => $oid]);
        return $post ? normalize_mongo($post) : null;
    }

    /**
     * Get all stories by a specific author ID.
     */
    public function forAuthor(string $authorId, int $limit = 50): array
    {
        $db = Database::connection();
        if (!$db) {
            return [];
        }

        $cursor = $db->posts->find(
            ['author.id' => $authorId],
            ['sort' => ['created_at' => -1], 'limit' => max(1, min(100, $limit))]
        );

        return array_map('normalize_mongo', iterator_to_array($cursor, false));
    }

    /**
     * List posts with optional status filter (admin).
     */
    public function all(int $limit = 100, ?string $status = null): array
    {
        $db = Database::connection();
        if (!$db) {
            return [];
        }

        $filter = [];
        if ($status !== null && in_array($status, ['draft', 'published'], true)) {
            $filter['status'] = $status;
        }

        $cursor = $db->posts->find($filter, [
            'sort' => ['created_at' => -1],
            'limit' => max(1, min(200, $limit)),
        ]);

        return array_map('normalize_mongo', iterator_to_array($cursor, false));
    }
}
