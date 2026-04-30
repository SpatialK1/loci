<?php
class MediaRepository {

    public function create(array $data): array {
        DB::insert('media', [
            'type'           => $data['type'],
            'title'          => $data['title'],
            'author'         => $data['author'] ?? null,
            'url'            => $data['url'] ?? null,
            'notes'          => $data['notes'] ?? null,
            'recommender_id' => $data['recommender_id'] ?? null,
            'status'         => $data['status'] ?? 'queue',
            'is_dead'        => $data['is_dead'] ?? 0,
            'is_paywalled'   => $data['is_paywalled'] ?? 0,
            'isbn'           => $data['isbn'] ?? null,
            'book_format'    => $data['book_format'] ?? null,
            'show_name'      => $data['show_name'] ?? null,
        ]);

        $id = DB::insertId();
        return $this->findById($id);
    }

    public function findById(int $id): ?array {
        $row = DB::queryFirstRow(
            "SELECT m.*, r.name AS recommender_name
             FROM media m
             LEFT JOIN recommenders r ON r.id = m.recommender_id
             WHERE m.id = %i",
            $id
        );

        if (!$row) return null;

        $row['tags'] = $this->getTagsForMedia($id);
        return $row;
    }

    public function getAll(array $filters = []): array {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['type'])) {
            $where[] = "m.type = %s";
            $params[] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $where[] = "m.status = %s";
            $params[] = $filters['status'];
        }

        if (!empty($filters['recommender_id'])) {
            $where[] = "m.recommender_id = %i";
            $params[] = $filters['recommender_id'];
        }

        $whereClause = implode(' AND ', $where);
        $query = "SELECT m.*, r.name AS recommender_name
                  FROM media m
                  LEFT JOIN recommenders r ON r.id = m.recommender_id
                  WHERE $whereClause
                  ORDER BY m.created_at DESC";

        $rows = DB::query($query, ...$params);

        foreach ($rows as &$row) {
            $row['tags'] = $this->getTagsForMedia($row['id']);
        }

        return $rows;
    }

    private function getTagsForMedia(int $mediaId): array {
        return DB::query(
            "SELECT t.id, t.name
             FROM tags t
             JOIN media_tags mt ON mt.tag_id = t.id
             WHERE mt.media_id = %i",
            $mediaId
        );
    }
}