<?php
class ListRepository extends BaseRepository {

    public function create(array $data): array {
        $token = bin2hex(random_bytes(32));

        DB::insert('lists', [
            'user_id'     => $data['user_id'] ?? null,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'share_token' => $token,
            'is_public'   => $data['is_public'] ?? 0,
        ]);

        $id = DB::insertId();
        return $this->findById($id, $data['user_id'] ?? null);
    }

    public function findById(int $id, ?int $currentUserId = null): ?array {
        $list = DB::queryFirstRow(
            "SELECT * FROM lists
             WHERE id = %i
             AND (
                 user_id = %i
                 OR is_public = 1
             )",
            $id,
            $currentUserId ?? 0
        );

        if (!$list) return null;
        $list = $this->castRow($list);
        $list['media'] = $this->getMediaForList($id, $currentUserId);
        return $list;
    }

    public function findByToken(string $token): ?array {
        $list = DB::queryFirstRow(
            "SELECT * FROM lists WHERE share_token = %s",
            $token
        );

        if (!$list) return null;
        $list = $this->castRow($list);
        $list['media'] = $this->getMediaForList($list['id'], null);
        return $list;
    }

    public function getAll(?int $currentUserId = null): array {
        if ($currentUserId) {
            $lists = DB::query(
                "SELECT * FROM lists
                 WHERE user_id = %i OR is_public = 1
                 ORDER BY created_at DESC",
                $currentUserId
            );
        } else {
            $lists = DB::query(
                "SELECT * FROM lists WHERE is_public = 1 ORDER BY created_at DESC"
            );
        }

        foreach ($lists as &$list) {
            $list = $this->castRow($list);
            $list['media'] = $this->getMediaForList($list['id'], $currentUserId);
        }

        return $lists;
    }

    public function update(int $id, int $userId, array $data): ?array {
        // Verify ownership
        $existing = DB::queryFirstRow(
            "SELECT id FROM lists WHERE id = %i AND user_id = %i",
            $id, $userId
        );
        if (!$existing) return null;

        $allowed = ['name', 'description', 'is_public'];

        $update = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $update[$field] = $data[$field];
            }
        }

        if (!empty($update)) {
            DB::update('lists', $update, 'id = %i', $id);
        }

        return $this->findById($id, $userId);
    }

    public function delete(int $id, int $userId): bool {
        DB::query(
            "DELETE FROM lists WHERE id = %i AND user_id = %i",
            $id, $userId
        );
        return DB::affectedRows() > 0;
    }

    public function addMedia(int $listId, int $mediaId, int $userId): void {
        // Verify list ownership
        $list = DB::queryFirstRow(
            "SELECT id FROM lists WHERE id = %i AND user_id = %i",
            $listId, $userId
        );
        if (!$list) return;

        DB::query(
            "INSERT IGNORE INTO media_lists (list_id, media_id) VALUES (%i, %i)",
            $listId,
            $mediaId
        );
    }

    public function removeMedia(int $listId, int $mediaId, int $userId): void {
        // Verify list ownership
        $list = DB::queryFirstRow(
            "SELECT id FROM lists WHERE id = %i AND user_id = %i",
            $listId, $userId
        );
        if (!$list) return;

        DB::query(
            "DELETE FROM media_lists WHERE list_id = %i AND media_id = %i",
            $listId,
            $mediaId
        );
    }

    private function castRow(array $row): array {
        $row = $this->castIntegers($row, ['id', 'user_id']);
        $row = $this->castBooleans($row, ['is_public']);
        return $row;
    }

    private function getMediaForList(int $listId, ?int $currentUserId = null): array {
        if ($currentUserId) {
            $rows = DB::query(
                "SELECT m.*
                 FROM media m
                 JOIN media_lists ml ON ml.media_id = m.id
                 WHERE ml.list_id = %i
                 AND (m.user_id = %i OR m.visibility = 'group' OR m.visibility = 'public')
                 ORDER BY m.created_at DESC",
                $listId,
                $currentUserId
            );
        } else {
            $rows = DB::query(
                "SELECT m.*
                 FROM media m
                 JOIN media_lists ml ON ml.media_id = m.id
                 WHERE ml.list_id = %i
                 AND m.visibility = 'public'
                 ORDER BY m.created_at DESC",
                $listId
            );
        }

        foreach ($rows as &$row) {
            $row = $this->castIntegers($row, ['id', 'recommender_id', 'user_id']);
            $row = $this->castBooleans($row, ['is_dead', 'is_paywalled']);
        }
        return $rows;
    }
}