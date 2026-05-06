<?php
abstract class BaseRepository {

    protected function castIntegers(array $row, array $fields): array {
        foreach ($fields as $field) {
            if (array_key_exists($field, $row)) {
                $row[$field] = $row[$field] !== null ? (int) $row[$field] : null;
            }
        }
        return $row;
    }

    protected function castBooleans(array $row, array $fields): array {
        foreach ($fields as $field) {
            if (array_key_exists($field, $row)) {
                $row[$field] = (bool) $row[$field];
            }
        }
        return $row;
    }
}