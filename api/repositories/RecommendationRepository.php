<?php
class RecommendationRepository extends BaseRepository {

    public function create(int $fromUserId, array $toUserIds, int $mediaId): array {
        $created = [];
        foreach ($toUserIds as $toUserId) {
            // Skip if recommendation already exists and is pending
            $existing = DB::queryFirstRow(
                "SELECT id FROM recommendations
                 WHERE from_user_id = %i
                 AND to_user_id = %i
                 AND media_id = %i
                 AND status = 'pending'",
                $fromUserId, $toUserId, $mediaId
            );
            if ($existing) continue;

            DB::insert('recommendations', [
                'from_user_id' => $fromUserId,
                'to_user_id'   => $toUserId,
                'media_id'     => $mediaId,
                'status'       => 'pending',
            ]);
            $created[] = $this->findById(DB::insertId());
        }
        return $created;
    }

    public function findById(int $id): ?array {
        $row = DB::queryFirstRow(
            "SELECT r.*,
                    fu.username AS from_username,
                    tu.username AS to_username,
                    m.title AS media_title,
                    m.type  AS media_type,
                    m.author AS media_author,
                    m.url   AS media_url
             FROM recommendations r
             JOIN users fu ON fu.id = r.from_user_id
             JOIN users tu ON tu.id = r.to_user_id
             JOIN media m  ON m.id  = r.media_id
             WHERE r.id = %i",
            $id
        );
        if (!$row) return null;
        return $this->castRow($row);
    }

    public function getPendingForUser(int $userId): array {
        $rows = DB::query(
            "SELECT r.*,
                    fu.username AS from_username,
                    m.title AS media_title,
                    m.type  AS media_type,
                    m.author AS media_author,
                    m.url   AS media_url,
                    m.notes AS media_notes
             FROM recommendations r
             JOIN users fu ON fu.id = r.from_user_id
             JOIN media m  ON m.id  = r.media_id
             WHERE r.to_user_id = %i AND r.status = 'pending'
             ORDER BY r.created_at DESC",
            $userId
        );
        return array_map([$this, 'castRow'], $rows);
    }

    public function getSentByUser(int $userId): array {
        $rows = DB::query(
            "SELECT r.*,
                    tu.username AS to_username,
                    m.title AS media_title,
                    m.type  AS media_type
             FROM recommendations r
             JOIN users tu ON tu.id = r.to_user_id
             JOIN media m  ON m.id  = r.media_id
             WHERE r.from_user_id = %i
             ORDER BY r.created_at DESC",
            $userId
        );
        return array_map([$this, 'castRow'], $rows);
    }

    public function accept(int $id, int $userId): ?array {
        // Verify this recommendation belongs to this user
        $rec = DB::queryFirstRow(
            "SELECT * FROM recommendations WHERE id = %i AND to_user_id = %i AND status = 'pending'",
            $id, $userId
        );
        if (!$rec) return null;

        // Get the original media item
        $media = DB::queryFirstRow("SELECT * FROM media WHERE id = %i", $rec['media_id']);
        if (!$media) return null;

        // Create a copy in the receiving user's archive
        DB::insert('media', [
            'user_id'                => $userId,
            'type'                   => $media['type'],
            'title'                  => $media['title'],
            'author'                 => $media['author'],
            'url'                    => $media['url'],
            'notes'                  => $media['notes'],
            'recommender_id'         => $media['recommender_id'],
            'status'                 => 'queue',
            'is_dead'                => 0,
            'is_paywalled'           => 0,
            'visibility'             => 'private',
            'recommended_by_user_id' => $rec['from_user_id'],
            'isbn'                   => $media['isbn'],
            'book_format'            => $media['book_format'],
            'show_name'              => $media['show_name'],
        ]);

        $newMediaId = DB::insertId();

        // Mark recommendation as accepted
        DB::update('recommendations', [
            'status'      => 'accepted',
            'resolved_at' => date('Y-m-d H:i:s'),
        ], 'id = %i', $id);

        return $this->findById($id);
    }

    public function decline(int $id, int $userId): bool {
        DB::query(
            "UPDATE recommendations
             SET status = 'declined', resolved_at = NOW()
             WHERE id = %i AND to_user_id = %i AND status = 'pending'",
            $id, $userId
        );
        return DB::affectedRows() > 0;
    }

    public function getEligibleRecipients(int $fromUserId): array {
        // Returns all active users except the sender who accept recommendations
        $rows = DB::query(
            "SELECT id, username, accept_recommendations
             FROM users
             WHERE id != %i AND is_active = 1
             ORDER BY username ASC",
            $fromUserId
        );
        return array_map([$this, 'castRow'], $rows);
    }

    private function castRow(array $row): array {
        $row = $this->castIntegers($row, ['id', 'from_user_id', 'to_user_id', 'media_id']);
        return $row;
    }
}