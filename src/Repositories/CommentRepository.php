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
            'status' => function_exists('get_setting') ? (string)get_setting('comments_policy', 'approved') : 'approved',
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

        $comments = array_map('normalize_mongo', iterator_to_array($cursor, false));
        if (empty($comments)) {
            return [];
        }

        // Batch-enrich commenter name, handle, and avatar from live users collection (by ID and handle)
        $userOids = [];
        $handles = [];
        foreach ($comments as $c) {
            $author = is_array($c['author'] ?? null) ? $c['author'] : [];
            $uid = (string)($author['id'] ?? '');
            if ($uid !== '' && ($uOid = oid($uid))) {
                $userOids[(string)$uOid] = $uOid;
            }
            $h = ltrim(trim((string)($author['handle'] ?? '')), '@');
            if ($h !== '') {
                $handles[strtolower($h)] = $h;
            }
        }

        if (!empty($userOids) || !empty($handles)) {
            $or = [];
            if (!empty($userOids)) {
                $or[] = ['_id' => ['$in' => array_values($userOids)]];
            }
            if (!empty($handles)) {
                $or[] = ['handle' => ['$in' => array_values($handles)]];
            }
            $filter = count($or) === 1 ? $or[0] : ['$or' => $or];
            $uCursor = $db->users->find(
                $filter,
                ['projection' => ['name' => 1, 'handle' => 1, 'avatar' => 1]]
            );
            $usersById = [];
            $usersByHandle = [];
            foreach ($uCursor as $uDoc) {
                $u = normalize_mongo($uDoc);
                $uId = (string)($u['_id'] ?? '');
                if ($uId !== '') {
                    $usersById[$uId] = $u;
                }
                $uHandle = strtolower(ltrim((string)($u['handle'] ?? ''), '@'));
                if ($uHandle !== '') {
                    $usersByHandle[$uHandle] = $u;
                }
            }

            foreach ($comments as &$c) {
                if (!isset($c['author']) || !is_array($c['author'])) {
                    continue;
                }
                $uid = (string)($c['author']['id'] ?? '');
                $uHandle = strtolower(ltrim((string)($c['author']['handle'] ?? ''), '@'));
                $liveUser = $usersById[$uid] ?? $usersByHandle[$uHandle] ?? null;
                if ($liveUser) {
                    $c['author']['name'] = (string)($liveUser['name'] ?? $c['author']['name'] ?? 'Reader');
                    $c['author']['handle'] = (string)($liveUser['handle'] ?? $c['author']['handle'] ?? '');
                    $c['author']['avatar'] = (string)($liveUser['avatar'] ?? $c['author']['avatar'] ?? '');
                }
            }
            unset($c);
        }

        return $comments;
    }

    /**
     * Get approved comments by a specific author across published stories only.
     */
    public function forAuthor(string $authorId, string $authorHandle = '', int $limit = 10): array
    {
        $db = Database::connection();
        if (!$db) {
            return [];
        }

        $authorId = trim($authorId);
        $authorHandle = ltrim(trim($authorHandle), '@');
        if ($authorId === '' && $authorHandle === '') {
            return [];
        }

        $filter = ['status' => 'approved'];
        if (oid($authorId)) {
            $filter['author.id'] = $authorId;
        } else {
            $or = [];
            if ($authorId !== '') {
                $or[] = ['author.id' => $authorId];
            }
            if ($authorHandle !== '' && preg_match('/^[a-zA-Z0-9_.\-]{1,50}$/', $authorHandle)) {
                $or[] = ['author.handle' => new \MongoDB\BSON\Regex('^' . preg_quote($authorHandle, '/') . '$', 'i')];
            }
            if (empty($or)) {
                return [];
            }
            if (count($or) === 1) {
                $filter += $or[0];
            } else {
                $filter['$or'] = $or;
            }
        }

        $limit = max(1, min(50, $limit));
        $cursor = $db->comments->find(
            $filter,
            ['sort' => ['created_at' => -1], 'limit' => $limit * 2]
        );
        $rawComments = array_map('normalize_mongo', iterator_to_array($cursor, false));
        if (empty($rawComments)) {
            return [];
        }

        $postOids = [];
        foreach ($rawComments as $c) {
            $oid = oid((string)($c['post_id'] ?? ''));
            if ($oid) {
                $postOids[(string)$oid] = $oid;
            }
        }
        if (empty($postOids)) {
            return [];
        }

        $postsCursor = $db->posts->find(
            ['_id' => ['$in' => array_values($postOids)], 'status' => 'published'],
            ['projection' => ['title' => 1, 'slug' => 1]]
        );
        $publishedPosts = [];
        foreach ($postsCursor as $pDoc) {
            $p = normalize_mongo($pDoc);
            $publishedPosts[(string)$p['_id']] = $p;
        }

        $visible = [];
        foreach ($rawComments as $c) {
            $pid = (string)($c['post_id'] ?? '');
            if (!isset($publishedPosts[$pid])) {
                continue;
            }
            $c['post_title'] = (string)($publishedPosts[$pid]['title'] ?? 'Story');
            $c['post_slug'] = (string)($publishedPosts[$pid]['slug'] ?? $pid);
            $visible[] = $c;
            if (count($visible) >= $limit) {
                break;
            }
        }

        return $visible;
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
