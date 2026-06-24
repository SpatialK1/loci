<?php
class UserRepository extends BaseRepository {

    public function create(array $data): array {
        DB::insert('users', [
            'username'               => $data['username'],
            'email'                  => $data['email'],
            'password_hash'          => password_hash($data['password'], PASSWORD_DEFAULT),
            'role'                   => $data['role'] ?? 'member',
            'archive_visibility'     => $data['archive_visibility'] ?? 'private',
            'accept_recommendations' => $data['accept_recommendations'] ?? 1,
            'is_active'              => 1,
        ]);
        $id = DB::insertId();
        return $this->findById($id);
    }

    public function findById(int $id): ?array {
        $row = DB::queryFirstRow(
            "SELECT * FROM users WHERE id = %i",
            $id
        );
        if (!$row) return null;
        return $this->castRow($row);
    }

    public function findByUsername(string $username): ?array {
        $row = DB::queryFirstRow(
            "SELECT * FROM users WHERE username = %s",
            $username
        );
        if (!$row) return null;
        return $this->castRow($row);
    }

    public function findByEmail(string $email): ?array {
        $row = DB::queryFirstRow(
            "SELECT * FROM users WHERE email = %s",
            $email
        );
        if (!$row) return null;
        return $this->castRow($row);
    }

    public function getAll(): array {
        $rows = DB::query("SELECT * FROM users ORDER BY created_at ASC");
        return array_map([$this, 'castRow'], $rows);
    }

    public function getActiveMembers(): array {
        $rows = DB::query(
            "SELECT id, username, email, role, archive_visibility, accept_recommendations, created_at
             FROM users WHERE is_active = 1 ORDER BY username ASC"
        );
        return array_map([$this, 'castRow'], $rows);
    }

    public function update(int $id, array $data): ?array {
        $allowed = [
            'username', 'email', 'role', 'archive_visibility',
            'accept_recommendations', 'is_active'
        ];
        $update = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $update[$field] = $data[$field];
            }
        }
        if (!empty($data['password'])) {
            $update['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if (!empty($update)) {
            DB::update('users', $update, 'id = %i', $id);
        }
        return $this->findById($id);
    }

    public function delete(int $id): bool {
        DB::query("DELETE FROM users WHERE id = %i", $id);
        return DB::affectedRows() > 0;
    }

    public function verifyPassword(int $id, string $password): bool {
        $row = DB::queryFirstRow(
            "SELECT password_hash FROM users WHERE id = %i",
            $id
        );
        if (!$row) return false;
        return password_verify($password, $row['password_hash']);
    }

    public function setResetToken(int $id): string {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        DB::update('users', [
            'reset_token'            => $token,
            'reset_token_expires_at' => $expires,
        ], 'id = %i', $id);
        return $token;
    }

    public function findByResetToken(string $token): ?array {
        $row = DB::queryFirstRow(
            "SELECT * FROM users
             WHERE reset_token = %s
             AND reset_token_expires_at > NOW()
             AND is_active = 1",
            $token
        );
        if (!$row) return null;
        return $this->castRow($row);
    }

    public function clearResetToken(int $id): void {
        DB::update('users', [
            'reset_token'            => null,
            'reset_token_expires_at' => null,
        ], 'id = %i', $id);
    }

    public function safeView(array $user): array {
        unset($user['password_hash'], $user['reset_token'], $user['reset_token_expires_at']);
        return $user;
    }

    private function castRow(array $row): array {
        $row = $this->castIntegers($row, ['id']);
        $row = $this->castBooleans($row, ['accept_recommendations', 'is_active']);
        return $row;
    }
}