<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

abstract class BaseModel
{
    protected $table;

    protected function db(): PDO
    {
        return Database::connection();
    }

    public function all(string $orderBy = 'id DESC'): array
    {
        $stmt = $this->db()->query("SELECT * FROM {$this->table} WHERE deleted_at IS NULL ORDER BY {$orderBy}");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db()->prepare("SELECT * FROM {$this->table} WHERE id = ? AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        $this->db()->prepare($sql)->execute(array_values($data));
        return (int) $this->db()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $sets = implode(', ', array_map(function ($column) {
            return "{$column} = ?";
        }, array_keys($data)));
        $values = array_values($data);
        $values[] = $id;
        $this->db()->prepare("UPDATE {$this->table} SET {$sets} WHERE id = ?")->execute($values);
    }

    public function delete(int $id): void
    {
        $this->db()->prepare("UPDATE {$this->table} SET deleted_at = NOW() WHERE id = ?")->execute([$id]);
    }
}
