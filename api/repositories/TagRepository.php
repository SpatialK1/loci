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
        // Remove existing tags for this media item
        DB::query("DELETE FROM media_tags WHERE media_id = %i", $mediaId);

        // Add new tags
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

    public function getAll(): array {
        $rows = DB::query("SELECT * FROM tags ORDER BY name ASC");
        foreach ($rows as &$row) {
            $row = $this->castIntegers($row, ['id']);
        }
        return $rows;
    }

    public function create(string $name): array {
        DB::insert('tags', ['name' => $name]);
        $id = DB::insertId();
        return ['id' => $id, 'name' => $name];
    }
    
    public function update(int $id, string $name): ?array {
        DB::update('tags', ['name' => $name], 'id = %i', $id);
        $row = DB::queryFirstRow("SELECT * FROM tags WHERE id = %i", $id);
        if (!$row) return null;
        return $this->castIntegers($row, ['id']);
    }
    
    public function delete(int $id): bool {
        DB::query("DELETE FROM tags WHERE id = %i", $id);
        return DB::affectedRows() > 0;
    }

    public function getMediaByTags(array $tagNames): array {
        $count = count($tagNames);

        $rows = DB::query(
            "SELECT m.*
             FROM media m
             JOIN media_tags mt ON mt.media_id = m.id
             JOIN tags t ON t.id = mt.tag_id
             WHERE t.name IN %ls
             GROUP BY m.id
             HAVING COUNT(DISTINCT t.id) = %i",
            $tagNames,
            $count
        );
        foreach ($rows as &$row) {
            $row = $this->castIntegers($row, ['id', 'recommender_id']);
            $row = $this->castBooleans($row, ['is_dead', 'is_paywalled']);
        }
        return $rows;
    }
}