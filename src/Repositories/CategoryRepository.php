<?php
declare(strict_types=1);

namespace BlogForge\Repositories;

use BlogForge\Database;
use MongoDB\BSON\UTCDateTime;

final class CategoryRepository
{
    /**
     * Retrieve all categories sorted by name.
     */
    public function all(): array
    {
        $db = Database::connection();
        if (!$db) {
            return [
                ['name' => 'Technology', 'slug' => 'technology'],
                ['name' => 'Lifestyle', 'slug' => 'lifestyle'],
                ['name' => 'Business', 'slug' => 'business'],
                ['name' => 'Education', 'slug' => 'education'],
                ['name' => 'Health', 'slug' => 'health'],
                ['name' => 'Travel', 'slug' => 'travel'],
                ['name' => 'Culture', 'slug' => 'culture'],
                ['name' => 'Self Improvement', 'slug' => 'self-improvement'],
            ];
        }

        $cursor = $db->categories->find([], ['sort' => ['name' => 1]]);
        return array_map('normalize_mongo', iterator_to_array($cursor, false));
    }

    /**
     * Create or update a category.
     */
    public function create(string $name): void
    {
        $db = Database::connection();
        if (!$db) {
            throw new \RuntimeException('Database is currently offline. Cannot create category.');
        }

        $name = trim($name);
        if (mb_strlen($name) < 2 || mb_strlen($name) > 50) {
            throw new \RuntimeException('Category name must be between 2 and 50 characters.');
        }

        $slug = slugify($name);
        $db->categories->updateOne(
            ['slug' => $slug],
            [
                '$set' => [
                    'name' => $name,
                    'slug' => $slug,
                    'updated_at' => new UTCDateTime(),
                ],
                '$setOnInsert' => [
                    'created_at' => new UTCDateTime(),
                ],
            ],
            ['upsert' => true]
        );
    }

    /**
     * Delete a category by slug.
     */
    public function delete(string $slug): bool
    {
        $db = Database::connection();
        if (!$db) {
            throw new \RuntimeException('Database is currently offline.');
        }

        $slug = trim($slug);
        $res = $db->categories->deleteOne(['slug' => $slug]);
        return $res->getDeletedCount() > 0;
    }
}
