<?php
class TagRepository {

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
        return DB::query("SELECT * FROM tags ORDER BY name ASC");
    }

    public function getMediaByTags(array $tagNames): array {
        $count = count($tagNames);

        return DB::query(
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
    }
}