<?php
class InvitationRepository extends BaseRepository {

    public function create(string $email, int $invitedByUserId): array {
        // Check if an active invitation already exists for this email
        $existing = DB::queryFirstRow(
            "SELECT * FROM invitations
             WHERE email = %s AND accepted_at IS NULL",
            $email
        );
        if ($existing) {
            return $this->castRow($existing);
        }

        $token = bin2hex(random_bytes(32));
        DB::insert('invitations', [
            'email'              => $email,
            'token'              => $token,
            'invited_by_user_id' => $invitedByUserId,
        ]);
        $id = DB::insertId();
        return $this->findById($id);
    }

    public function findById(int $id): ?array {
        $row = DB::queryFirstRow(
            "SELECT i.*, u.username AS invited_by_username
             FROM invitations i
             JOIN users u ON u.id = i.invited_by_user_id
             WHERE i.id = %i",
            $id
        );
        if (!$row) return null;
        return $this->castRow($row);
    }

    public function findByToken(string $token): ?array {
        $row = DB::queryFirstRow(
            "SELECT i.*, u.username AS invited_by_username
             FROM invitations i
             JOIN users u ON u.id = i.invited_by_user_id
             WHERE i.token = %s AND i.accepted_at IS NULL",
            $token
        );
        if (!$row) return null;
        return $this->castRow($row);
    }

    public function getAll(): array {
        $rows = DB::query(
            "SELECT i.*, u.username AS invited_by_username
             FROM invitations i
             JOIN users u ON u.id = i.invited_by_user_id
             ORDER BY i.created_at DESC"
        );
        return array_map([$this, 'castRow'], $rows);
    }

    public function accept(string $token): bool {
        $row = $this->findByToken($token);
        if (!$row) return false;
        DB::update('invitations',
            ['accepted_at' => date('Y-m-d H:i:s')],
            'token = %s', $token
        );
        return true;
    }

    public function delete(int $id): bool {
        DB::query("DELETE FROM invitations WHERE id = %i", $id);
        return DB::affectedRows() > 0;
    }

    public function sendInvitationEmail(string $email, string $token, string $inviterUsername): bool {
        $siteTitle = DB::queryFirstField("SELECT `value` FROM settings WHERE `key` = 'site_title'") ?? 'Loci';
        $mailFrom  = DB::queryFirstField("SELECT `value` FROM settings WHERE `key` = 'mail_from'") ?? '';
        $mailName  = DB::queryFirstField("SELECT `value` FROM settings WHERE `key` = 'mail_from_name'") ?? 'Loci';

        if (empty($mailFrom)) return false;

        $link    = 'https://' . $_SERVER['HTTP_HOST'] . '/register?token=' . $token;
        $subject = $inviterUsername . ' invited you to join ' . $siteTitle;
        $body    = "Hi,\n\n"
                 . $inviterUsername . " has invited you to join their " . $siteTitle . " archive.\n\n"
                 . "Click the link below to create your account:\n"
                 . $link . "\n\n"
                 . "This invitation does not expire.\n\n"
                 . "— " . $siteTitle;

        $headers = "From: " . $mailName . " <" . $mailFrom . ">\r\n"
                 . "Reply-To: " . $mailFrom . "\r\n"
                 . "Content-Type: text/plain; charset=UTF-8\r\n";

        return mail($email, $subject, $body, $headers);
    }

    private function castRow(array $row): array {
        return $this->castIntegers($row, ['id', 'invited_by_user_id']);
    }
}