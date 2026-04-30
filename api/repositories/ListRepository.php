<?php
class ListRepository {

    public function create(array $data): array {
        $token = bin2hex(random_bytes(32));

        DB::insert('lists', [
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'share_token' => $token,
            'is_public'   => $data['is_public'] ?? 0,
        ]);

        $id = DB::insertId();
        return $this->findById($id);
    }

    public function findById(int $id): ?array {
        $list = DB::queryFirstRow(
            "SELECT * FROM lists WHERE id = %i",
            $id
        );

        if (!$list) return null;

        $list['media'] = $this->getMediaForList($id);
        return $list;
    }

    public function findByToken(string $token): ?array {
        $list = DB::queryFirstRow(
            "SELECT * FROM lists WHERE share_token = %s",
            $token
        );

        if (!$list) return null;

        $list['media'] = $this->getMediaForList($list['id']);
        return $list;
    }

    public function getAll(): array {
        $lists = DB::query("SELECT * FROM lists ORDER BY created_at DESC");

        foreach ($lists as &$list) {
            $list['media'] = $this->getMediaForList($list['id']);
        }

        return $lists;
    }

    public function update(int $id, array $data): ?array {
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
    
        return $this->findById($id);
    }
    
    public function delete(int $id): bool {
        DB::query("DELETE FROM lists WHERE id = %i", $id);
        return DB::affectedRows() > 0;
    }

    public function addMedia(int $listId, int $mediaId): void {
        DB::query(
            "INSERT IGNORE INTO media_lists (list_id, media_id) VALUES (%i, %i)",
            $listId,
            $mediaId
        );
    }

    public function removeMedia(int $listId, int $mediaId): void {
        DB::query(
            "DELETE FROM media_lists WHERE list_id = %i AND media_id = %i",
            $listId,
            $mediaId
        );
    }

    private function getMediaForList(int $listId): array {
        return DB::query(
            "SELECT m.*
             FROM media m
             JOIN media_lists ml ON ml.media_id = m.id
             WHERE ml.list_id = %i
             ORDER BY m.created_at DESC",
            $listId
        );
    }
}