<?php
class TagRepository extends BaseRepository {

    public function findOrCreate(string $name): int {
        $tag = DB::queryFirstRow(
            "SELECT id FROM tags WHERE name = %s",
            $name
        );

        if ($tag) return $tag['id'];

        DB::insert('tags', ['name' => $name]);
        return DB::insertId();
    }

    public function syncTagsForMedia(int $mediaId, array $tagNames): void {
        DB::query("DELETE FROM media_tags WHERE media_id = %i", $mediaId);

        foreach ($tagNames as $name) {
            $name = trim($name);
            if (!$name) continue;

            $tagId = $this->findOrCreate($name);
            DB::insert('media_tags', [
                'media_id' => $mediaId,
                'tag_id'   => $tagId,
            ]);
        }
    }

    public function getAll(?int $currentUserId = null): array {
        // Return tags that are attached to media visible to the current user
        if ($currentUserId) {
            $rows = DB::query(
                "SELECT DISTINCT t.id, t.name
                 FROM tags t
                 JOIN media_tags mt ON mt.tag_id = t.id
                 JOIN media m ON m.id = mt.media_id
                 WHERE m.user_id = %i
                    OR m.visibility = 'group'
                    OR m.visibility = 'public'
                 ORDER BY t.name ASC",
                $currentUserId
            );
        } else {
            // Unauthenticated — only tags on public media
            $rows = DB::query(
                "SELECT DISTINCT t.id, t.name
                 FROM tags t
                 JOIN media_tags mt ON mt.tag_id = t.id
                 JOIN media m ON m.id = mt.media_id
                 WHERE m.visibility = 'public'
                 ORDER BY t.name ASC"
            );
        }
        foreach ($rows as &$row) {
            $row = $this->castIntegers($row, ['id']);
        }
        return $rows;
    }

    public function create(string $name): array {
        try {
            DB::insert('tags', ['name' => $name]);
            $id = DB::insertId();
            return ['id' => (int) $id, 'name' => $name];
        } catch (\Exception $e) {
            if ($this->isDuplicateEntryError($e)) {
                return ['error' => 'A tag with that name already exists'];
            }
            throw $e;
        }
    }

    public function update(int $id, string $name): array {
        try {
            DB::update('tags', ['name' => $name], 'id = %i', $id);
            $row = DB::queryFirstRow("SELECT * FROM tags WHERE id = %i", $id);
            if (!$row) return ['error' => 'Tag not found'];
            return $this->castIntegers($row, ['id']);
        } catch (\Exception $e) {
            if ($this->isDuplicateEntryError($e)) {
                return ['error' => 'A tag with that name already exists'];
            }
            throw $e;
        }
    }

    public function delete(int $id): bool {
        DB::query("DELETE FROM tags WHERE id = %i", $id);
        return DB::affectedRows() > 0;
    }

    public function getMediaByTags(array $tagNames, ?int $currentUserId = null): array {
        $count = count($tagNames);

        if ($currentUserId) {
            $rows = DB::query(
                "SELECT m.*
                 FROM media m
                 JOIN media_tags mt ON mt.media_id = m.id
                 JOIN tags t ON t.id = mt.tag_id
                 WHERE t.name IN %ls
                 AND (m.user_id = %i OR m.visibility = 'group' OR m.visibility = 'public')
                 GROUP BY m.id
                 HAVING COUNT(DISTINCT t.id) = %i",
                $tagNames,
                $currentUserId,
                $count
            );
        } else {
            $rows = DB::query(
                "SELECT m.*
                 FROM media m
                 JOIN media_tags mt ON mt.media_id = m.id
                 JOIN tags t ON t.id = mt.tag_id
                 WHERE t.name IN %ls
                 AND m.visibility = 'public'
                 GROUP BY m.id
                 HAVING COUNT(DISTINCT t.id) = %i",
                $tagNames,
                $count
            );
        }

        foreach ($rows as &$row) {
            $row = $this->castIntegers($row, ['id', 'recommender_id']);
            $row = $this->castBooleans($row, ['is_dead', 'is_paywalled']);
        }
        return $rows;
    }
}