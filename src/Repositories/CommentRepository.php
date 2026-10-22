<?php
declare(strict_types=1);

namespace BlogForge\Repositories;

use BlogForge\Database;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

final class CommentRepository
{
    /**
     * Add a comment to a story.
     */
    public function add(string $postId, string $body, array $user): string
    {
        $db = Database::connection();
        if (!$db) {
            throw new \RuntimeException('Database is currently offline. Cannot post response.');
        }

        $postId = trim($postId);
        if ($postId === '') {
            throw new \InvalidArgumentException('Missing story ID for response.');
        }

        $body = trim($body);
        if (mb_strlen($body) < 2 || mb_strlen($body) > 2000) {
            throw new \RuntimeException('Comment must be between 2 and 2,000 characters.');
        }

        $doc = [
            'post_id' => $postId,
            'body' => $body,
            'author' => [
                'id' => (string)$user['id'],
                'name' => (string)$user['name'],
                'handle' => (string)($user['handle'] ?? ''),
                'avatar' => (string)($user['avatar'] ?? ''),
            ],
            'status' => 'approved',
            'created_at' => new UTCDateTime(),
            'updated_at' => new UTCDateTime(),
        ];

        $result = $db->comments->insertOne($doc);

        $oid = oid($postId);
        if ($oid) {
            $db->posts->updateOne(['_id' => $oid], ['$inc' => ['comments' => 1]]);
        }

        return (string)$result->getInsertedId();
    }

    /**
     * Get approved comments for a specific post, normalized to native PHP types.
     */
    public function forPost(string $postId): array
    {
        $db = Database::connection();
        if (!$db) {
            return [];
        }

        $postId = trim($postId);
        if ($postId === '') {
            return [];
        }

        $cursor = $db->comments->find(
            ['post_id' => $postId, 'status' => 'approved'],
            ['sort' => ['created_at' => -1], 'limit' => 100]
        );

        return array_map('normalize_mongo', iterator_to_array($cursor, false));
    }

    /**
     * Retrieve all comments for administrative moderation.
     */
    public function all(int $limit = 100): array
    {
        $db = Database::connection();
        if (!$db) {
            return [];
        }

        $cursor = $db->comments->find(
            [],
            ['sort' => ['created_at' => -1], 'limit' => max(1, min(200, $limit))]
        );

        return array_map('normalize_mongo', iterator_to_array($cursor, false));
    }

    /**
     * Update comment status (approved, pending, spam).
     */
    public function updateStatus(string $commentId, string $status): bool
    {
        $db = Database::connection();
        if (!$db) {
            throw new \RuntimeException('Database is currently offline.');
        }

        $oid = oid($commentId);
        if (!$oid) {
            throw new \InvalidArgumentException('Invalid comment ID.');
        }

        if (!in_array($status, ['approved', 'pending', 'spam'], true)) {
            throw new \InvalidArgumentException('Invalid comment status.');
        }

        $result = $db->comments->updateOne(
            ['_id' => $oid],
            ['$set' => ['status' => $status, 'updated_at' => new UTCDateTime()]]
        );

        return $result->getMatchedCount() > 0;
    }

    /**
     * Delete a comment by ID (author or administrator).
     */
    public function delete(string $commentId, array $user): bool
    {
        $db = Database::connection();
        if (!$db) {
            throw new \RuntimeException('Database is currently offline.');
        }

        $oid = oid($commentId);
        if (!$oid) {
            throw new \InvalidArgumentException('Invalid comment ID.');
        }

        $comment = $db->comments->findOne(['_id' => $oid]);
        if (!$comment) {
            throw new \RuntimeException('Comment not found.');
        }

        $isAdmin = ($user['role'] ?? '') === 'admin';
        $isAuthor = ((string)($comment['author']['id'] ?? '')) === (string)$user['id'];

        if (!$isAdmin && !$isAuthor) {
            throw new \RuntimeException('You do not have permission to delete this comment.');
        }

        $db->comments->deleteOne(['_id' => $oid]);

        // Decrement comment count if post exists and comment was approved
        if (($comment['status'] ?? '') === 'approved' && !empty($comment['post_id'])) {
            $postOid = oid((string)$comment['post_id']);
            if ($postOid) {
                $db->posts->updateOne(['_id' => $postOid], ['$inc' => ['comments' => -1]]);
            }
        }

        return true;
    }
}
