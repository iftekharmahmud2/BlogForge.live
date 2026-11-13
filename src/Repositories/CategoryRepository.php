<?php
declare(strict_types=1);

namespace BlogForge\Repositories;

use BlogForge\Database;
use MongoDB\BSON\UTCDateTime;

final class CategoryRepository
{
    public const DEFAULT_NAMES = [
        'Technology',
        'Lifestyle',
        'Business',
        'Education',
        'Health',
        'Travel',
        'Culture',
        'Self Improvement',
    ];

    /**
     * Build fallback category rows from the built-in defaults.
     */
    public static function defaultRows(): array
    {
        return array_map(
            static fn (string $name): array => [
                'name' => $name,
                'slug' => slugify($name),
            ],
            self::DEFAULT_NAMES
        );
    }

    /**
     * Retrieve all categories sorted by name.
     */
    public function all(): array
    {
        $db = Database::connection();
        if (!$db) {
            return self::defaultRows();
        }

        try {
            $cursor = $db->categories->find([], ['sort' => ['name' => 1]]);
            return array_map('normalize_mongo', iterator_to_array($cursor, false));
        } catch (\Throwable $e) {
            error_log('[BlogForge] CategoryRepository::all error: ' . $e->getMessage());
            return self::defaultRows();
        }
    }

    /**
     * Retrieve category display names for writer and discovery dropdowns, falling back to defaults if none exist.
     * Optionally ensures an extra active/saved category name is present without duplicates.
     */
    public function names(?string $includeName = null): array
    {
        $names = [];
        $seen = [];

        foreach ($this->all() as $cat) {
            $name = trim((string)($cat['name'] ?? ''));
            $key = strtolower($name);
            if ($name !== '' && !isset($seen[$key])) {
                $seen[$key] = true;
                $names[] = $name;
            }
        }

        if (empty($names)) {
            $names = self::DEFAULT_NAMES;
            foreach ($names as $name) {
                $seen[strtolower($name)] = true;
            }
        }

        $extra = trim((string)$includeName);
        if ($extra !== '' && !isset($seen[strtolower($extra)])) {
            $names[] = $extra;
        }

        return $names;
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

        $name = trim(preg_replace('/\s+/u', ' ', $name) ?? $name);
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

    /**
     * Admin: Rename or update a category and its slug.
     */
    public function update(string $oldSlug, string $name, ?string $newSlug = null): void
    {
        $db = Database::connection();
        if (!$db) {
            throw new \RuntimeException('Database is currently offline.');
        }

        $oldSlug = trim($oldSlug);
        $name = trim(preg_replace('/\s+/u', ' ', $name) ?? $name);
        if (mb_strlen($name) < 2 || mb_strlen($name) > 50) {
            throw new \RuntimeException('Category name must be between 2 and 50 characters.');
        }

        $newSlug = ($newSlug !== null && trim($newSlug) !== '') ? slugify($newSlug) : slugify($name);

        $db->categories->updateOne(
            ['slug' => $oldSlug],
            [
                '$set' => [
                    'name' => $name,
                    'slug' => $newSlug,
                    'updated_at' => new UTCDateTime(),
                ]
            ]
        );

        // Also update any posts categorized under the old category name or slug
        $db->posts->updateMany(
            ['$or' => [['category' => $oldSlug], ['category_slug' => $oldSlug]]],
            ['$set' => ['category' => $name, 'category_slug' => $newSlug]]
        );
    }
}
