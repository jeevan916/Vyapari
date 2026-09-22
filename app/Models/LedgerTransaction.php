<?php

declare(strict_types=1);

namespace App\Models;

final class LedgerTransaction extends BaseModel
{
    protected $table = 'transactions';

    public const TYPES = [
        'maal-liya' => 'Maal Liya',
        'maal-return' => 'Maal Return',
        'cash-diya' => 'Cash Diya',
        'bank-payment-diya' => 'Bank Payment Diya',
        'fine-diya' => 'Fine Diya',
        'cash-rate-cut' => 'Cash Rate Cut',
        'bill-rate-cut' => 'Bill Rate Cut',
        'opening-balance' => 'Opening Balance',
    ];

    public function search(array $filters = []): array
    {
        $where = ['t.deleted_at IS NULL'];
        $params = [];
        if (!empty($filters['vyapari_id'])) {
            $where[] = 't.vyapari_id = ?';
            $params[] = (int) $filters['vyapari_id'];
        }
        if (!empty($filters['start_date'])) {
            $where[] = 't.transaction_date >= ?';
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where[] = 't.transaction_date <= ?';
            $params[] = $filters['end_date'];
        }

        $sql = 'SELECT t.*, v.vyapari_name FROM transactions t INNER JOIN vyaparis v ON v.id = t.vyapari_id WHERE ' . implode(' AND ', $where) . ' ORDER BY t.transaction_date DESC, t.id DESC';
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function hardDeleteForVyapari(int $vyapariId): void
    {
        $this->db()->prepare('DELETE FROM transactions WHERE vyapari_id = ?')->execute([$vyapariId]);
    }

    public function deleteFromDate(int $vyapariId, string $date): void
    {
        $this->db()->prepare('DELETE FROM transactions WHERE vyapari_id = ? AND transaction_date >= ?')->execute([$vyapariId, $date]);
    }
}
