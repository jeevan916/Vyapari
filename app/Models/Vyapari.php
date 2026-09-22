<?php

declare(strict_types=1);

namespace App\Models;

final class Vyapari extends BaseModel
{
    protected $table = 'vyaparis';

    public function search(?string $q = null): array
    {
        if (!$q) {
            return $this->all('vyapari_name ASC');
        }

        $like = '%' . $q . '%';
        $stmt = $this->db()->prepare(
            'SELECT * FROM vyaparis WHERE deleted_at IS NULL AND (vyapari_name LIKE ? OR company_name LIKE ? OR primary_number LIKE ?) ORDER BY vyapari_name ASC'
        );
        $stmt->execute([$like, $like, $like]);
        return $stmt->fetchAll();
    }
}
