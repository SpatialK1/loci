<?php
class MediaRepository extends BaseRepository {

    public function create(array $data): array {
        try {
            DB::insert('media', [
                'user_id'        => $data['user_id'] ?? null,
                'type'           => $data['type'],
                'title'          => $data['title'],
                'author'         => $data['author'] ?? null,
                'url'            => $data['url'] ?? null,
                'notes'          => $data['notes'] ?? null,
                'recommender_id' => $data['recommender_id'] ?? null,
                'status'         => $data['status'] ?? 'queue',
                'is_dead'        => $data['is_dead'] ?? 0,
                'is_paywalled'   => $data['is_paywalled'] ?? 0,
                'visibility'     => $data['visibility'] ?? 'group',
                'isbn'           => $data['isbn'] ?? null,
                'book_format'    => $data['book_format'] ?? null,
                'show_name'      => $data['show_name'] ?? null,
            ]);
            $id = DB::insertId();
            return $this->findById($id);
        } catch (\Exception $e) {
            if ($this->isDuplicateEntryError($e)) {
                return ['error' => 'That URL already exists in your archive'];
            }
            throw $e;
        }
    }

    public function findById(int $id, ?int $currentUserId = null): ?array {
        $row = DB::queryFirstRow(
            "SELECT m.*, r.name AS recommender_name
             FROM media m
             LEFT JOIN recommenders r ON r.id = m.recommender_id
             WHERE m.id = %i
             AND (
                 m.user_id = %i
                 OR m.visibility = 'group'
                 OR m.visibility = 'public'
             )",
            $id,
            $currentUserId ?? 0
        );

        if (!$row) return null;
        $row = $this->castRow($row);
        $row['tags'] = $this->getTagsForMedia($id);
        return $row;
    }

    public function findByIdOwned(int $id, int $userId): ?array {
        $row = DB::queryFirstRow(
            "SELECT m.*, r.name AS recommender_name
             FROM media m
             LEFT JOIN recommenders r ON r.id = m.recommender_id
             WHERE m.id = %i AND m.user_id = %i",
            $id, $userId
        );
        if (!$row) return null;
        $row = $this->castRow($row);
        $row['tags'] = $this->getTagsForMedia($id);
        return $row;
    }

    public function update(int $id, int $userId, array $data): array {
        // Verify ownership
        $existing = DB::queryFirstRow(
            "SELECT id FROM media WHERE id = %i AND user_id = %i",
            $id, $userId
        );
        if (!$existing) {
            return ['error' => 'Not found or permission denied'];
        }

        $allowed = [
            'title', 'author', 'url', 'notes', 'recommender_id',
            'status', 'consumed_at', 'is_dead', 'is_paywalled',
            'isbn', 'book_format', 'show_name', 'visibility'
        ];

        $update = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $update[$field] = $data[$field];
            }
        }

        try {
            if (!empty($update)) {
                DB::update('media', $update, 'id = %i', $id);
            }

            if (array_key_exists('tags', $data)) {
                $tags = new TagRepository();
                $tags->syncTagsForMedia($id, $data['tags']);
            }

            if (array_key_exists('recommender', $data)) {
                $recommenders = new RecommenderRepository();
                $recommender_id = $recommenders->findOrCreate($data['recommender']);
                DB::update('media', ['recommender_id' => $recommender_id], 'id = %i', $id);
            }

            return $this->findById($id, $userId);
        } catch (\Exception $e) {
            if ($this->isDuplicateEntryError($e)) {
                return ['error' => 'That URL already exists in your archive'];
            }
            throw $e;
        }
    }

    public function delete(int $id, int $userId): bool {
        DB::query(
            "DELETE FROM media WHERE id = %i AND user_id = %i",
            $id, $userId
        );
        return DB::affectedRows() > 0;
    }

    public function getAll(array $filters = [], ?int $currentUserId = null): array {
        $where  = [];
        $params = [];

        // Visibility scoping — own entries plus group/public entries from others
        if ($currentUserId) {
            $where[]  = "(m.user_id = %i OR m.visibility = 'group' OR m.visibility = 'public')";
            $params[] = $currentUserId;
        } else {
            // Unauthenticated — only public entries
            $where[] = "m.visibility = 'public'";
        }

        if (!empty($filters['type'])) {
            $where[]  = "m.type = %s";
            $params[] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $where[]  = "m.status = %s";
            $params[] = $filters['status'];
        }

        if (!empty($filters['recommender_id'])) {
            $where[]  = "m.recommender_id = %i";
            $params[] = $filters['recommender_id'];
        }

        if (!empty($filters['recommender'])) {
            $where[]  = "r.name = %s";
            $params[] = $filters['recommender'];
        }

        if (!empty($filters['tag'])) {
            $where[]  = "EXISTS (SELECT 1 FROM media_tags mt JOIN tags t ON t.id = mt.tag_id WHERE mt.media_id = m.id AND t.name = %s)";
            $params[] = $filters['tag'];
        }

        // Filter to only own entries
        if (!empty($filters['mine']) && $currentUserId) {
            $where[]  = "m.user_id = %i";
            $params[] = $currentUserId;
        }

        $allowedSorts = [
            'created_at'  => 'm.created_at',
            'title'       => 'm.title',
            'type'        => 'm.type',
            'status'      => 'm.status',
            'recommender' => 'r.name',
            'show_name'   => 'm.show_name',
        ];

        $sortBy  = $allowedSorts[$filters['sort'] ?? ''] ?? 'm.created_at';
        $sortDir = strtoupper($filters['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        $whereClause = implode(' AND ', $where);
        $query = "SELECT m.*, r.name AS recommender_name
                  FROM media m
                  LEFT JOIN recommenders r ON r.id = m.recommender_id
                  WHERE $whereClause
                  ORDER BY $sortBy $sortDir";

        $rows = DB::query($query, ...$params);

        foreach ($rows as &$row) {
            $row = $this->castRow($row);
            $row['tags'] = $this->getTagsForMedia($row['id']);
        }

        return $rows;
    }

    private function castRow(array $row): array {
        $row = $this->castIntegers($row, ['id', 'recommender_id', 'user_id']);
        $row = $this->castBooleans($row, ['is_dead', 'is_paywalled']);
        return $row;
    }

    private function getTagsForMedia(int $mediaId): array {
        $rows = DB::query(
            "SELECT t.id, t.name
             FROM tags t
             JOIN media_tags mt ON mt.tag_id = t.id
             WHERE mt.media_id = %i",
            $mediaId
        );
        foreach ($rows as &$row) {
            $row = $this->castIntegers($row, ['id']);
        }
        return $rows;
    }
}