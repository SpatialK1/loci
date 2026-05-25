<?php
class SettingsRepository extends BaseRepository {

    public function getAll(): array {
        $rows = DB::query("SELECT * FROM settings");
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key']] = $this->castValue($row['key'], $row['value']);
        }
        return $settings;
    }

    public function get(string $key): mixed {
        $row = DB::queryFirstRow(
            "SELECT * FROM settings WHERE `key` = %s",
            $key
        );
        if (!$row) return null;
        return $this->castValue($key, $row['value']);
    }

    public function set(string $key, mixed $value): array {
        DB::query(
            "INSERT INTO settings (`key`, `value`) VALUES (%s, %s)
             ON DUPLICATE KEY UPDATE `value` = %s",
            $key, (string) $value, (string) $value
        );
        return $this->getAll();
    }

    public function setMany(array $data): array {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
        return $this->getAll();
    }

    private function castValue(string $key, string $value): mixed {
        $booleans = ['site_public'];
        $integers = ['items_per_page'];
        $floats   = ['font_size'];

        if (in_array($key, $booleans)) return $value === 'true';
        if (in_array($key, $integers)) return (int) $value;
        if (in_array($key, $floats))   return (float) $value;
        return $value;
    }
}