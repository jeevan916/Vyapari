<?php

declare(strict_types=1);

namespace App\Models;

final class Product extends BaseModel
{
    protected $table = 'products';

    public function withVyapari(): array
    {
        $stmt = $this->db()->query(
            'SELECT p.*, v.vyapari_name FROM products p LEFT JOIN vyaparis v ON v.id = p.vyapari_id WHERE p.deleted_at IS NULL ORDER BY p.product_name ASC'
        );
        return $stmt->fetchAll();
    }

    public function byVyapari(int $vyapariId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM products WHERE deleted_at IS NULL AND (vyapari_id = ? OR vyapari_id IS NULL) ORDER BY product_name ASC'
        );
        $stmt->execute([$vyapariId]);
        return $stmt->fetchAll();
    }
}
