<?php
class RecommenderRepository extends BaseRepository {

    public function findOrCreate(string $name): int {
        $recommender = DB::queryFirstRow(
            "SELECT id FROM recommenders WHERE name = %s",
            $name
        );

        if ($recommender) return $recommender['id'];

        DB::insert('recommenders', ['name' => $name]);
        return DB::insertId();
    }

    public function getAll(): array {
        $rows = DB::query("SELECT * FROM recommenders ORDER BY name ASC");
        foreach ($rows as &$row) {
            $row = $this->castIntegers($row, ['id']);
        }
        return $rows;
    }

    public function findById(int $id): ?array {
        return DB::queryFirstRow(
            "SELECT * FROM recommenders WHERE id = %i",
            $id
        );
    }
}