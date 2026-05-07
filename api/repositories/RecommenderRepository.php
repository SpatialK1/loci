<?php
class RecommenderRepository extends BaseRepository {

    public function findOrCreate(string $name): int {
        $recommender = DB::queryFirstRow(
            "SELECT id FROM recommenders WHERE name = %s",
            $name
        );

        if ($recommender) return (int) $recommender['id'];

        DB::insert('recommenders', ['name' => $name]);
        return (int) DB::insertId();
    }

    public function getAll(): array {
        $rows = DB::query("SELECT * FROM recommenders ORDER BY name ASC");
        foreach ($rows as &$row) {
            $row = $this->castIntegers($row, ['id']);
        }
        return $rows;
    }

    public function findById(int $id): ?array {
        $row = DB::queryFirstRow(
            "SELECT * FROM recommenders WHERE id = %i",
            $id
        );
        if (!$row) return null;
        return $this->castIntegers($row, ['id']);
    }

    public function create(string $name): array {
        try {
            DB::insert('recommenders', ['name' => $name]);
            $id = DB::insertId();
            return ['id' => (int) $id, 'name' => $name];
        } catch (\Exception $e) {
            if ($this->isDuplicateEntryError($e)) {
                return ['error' => 'A recommender with that name already exists'];
            }
            throw $e;
        }
    }

    public function update(int $id, string $name): array {
        try {
            DB::update('recommenders', ['name' => $name], 'id = %i', $id);
            $row = DB::queryFirstRow("SELECT * FROM recommenders WHERE id = %i", $id);
            if (!$row) return ['error' => 'Recommender not found'];
            return $this->castIntegers($row, ['id']);
        } catch (\Exception $e) {
            if ($this->isDuplicateEntryError($e)) {
                return ['error' => 'A recommender with that name already exists'];
            }
            throw $e;
        }
    }

    public function delete(int $id): bool {
        DB::query("DELETE FROM recommenders WHERE id = %i", $id);
        return DB::affectedRows() > 0;
    }
}