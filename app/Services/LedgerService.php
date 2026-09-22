<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

final class LedgerService
{
    public function totals(int $vyapariId, ?string $startDate = null, ?string $endDate = null): array
    {
        $where = ['deleted_at IS NULL', 'vyapari_id = ?'];
        $params = [$vyapariId];
        if ($startDate) {
            $where[] = 'transaction_date >= ?';
            $params[] = $startDate;
        }
        if ($endDate) {
            $where[] = 'transaction_date <= ?';
            $params[] = $endDate;
        }

        $stmt = Database::connection()->prepare(
            'SELECT 
                COALESCE(SUM(net_weight), 0) AS ntwt,
                COALESCE(SUM(cash_amount), 0) AS cash,
                COALESCE(SUM(rtgs_amount), 0) AS rtgs,
                COALESCE(SUM(balance_cat_995), 0) AS cat_995,
                COALESCE(SUM(balance_cat_995 * 0.995), 0) AS gold_999
            FROM transactions WHERE ' . implode(' AND ', $where)
        );
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function sideTotals(int $vyapariId, string $side, ?string $startDate, ?string $endDate): array
    {
        $where = ['deleted_at IS NULL', 'vyapari_id = ?', 'liya_diya = ?'];
        $params = [$vyapariId, $side];
        if ($startDate) {
            $where[] = 'transaction_date >= ?';
            $params[] = $startDate;
        }
        if ($endDate) {
            $where[] = 'transaction_date <= ?';
            $params[] = $endDate;
        }

        $stmt = Database::connection()->prepare(
            'SELECT 
                COALESCE(SUM(net_weight), 0) AS ntwt,
                COALESCE(SUM(cash_amount), 0) AS cash,
                COALESCE(SUM(rtgs_amount), 0) AS rtgs,
                COALESCE(SUM(balance_cat_995), 0) AS cat_995,
                COALESCE(SUM(balance_cat_995 * 0.995), 0) AS gold_999
            FROM transactions WHERE ' . implode(' AND ', $where)
        );
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function dashboardTotals(): array
    {
        $stmt = Database::connection()->query(
            'SELECT v.id, v.vyapari_name, v.company_name,
                COALESCE(SUM(t.cash_amount), 0) AS cash,
                COALESCE(SUM(t.rtgs_amount), 0) AS rtgs,
                COALESCE(SUM(t.balance_cat_995 * 0.995), 0) AS gold_999
            FROM vyaparis v
            LEFT JOIN transactions t ON t.vyapari_id = v.id AND t.deleted_at IS NULL
            WHERE v.deleted_at IS NULL
            GROUP BY v.id, v.vyapari_name, v.company_name
            ORDER BY v.vyapari_name ASC'
        );
        return $stmt->fetchAll();
    }
}
