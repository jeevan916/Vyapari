<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\LedgerTransaction;
use App\Models\Vyapari;
use App\Services\LedgerService;

final class LedgerController extends Controller
{
    public function show(): void
    {
        Auth::requireLogin();
        $vyaparis = (new Vyapari())->all('vyapari_name ASC');
        $vyapariId = (int) ($_GET['vyapari_id'] ?? ($vyaparis[0]['id'] ?? 0));
        $start = $_GET['start_date'] ?? null;
        $end = $_GET['end_date'] ?? date('Y-m-d');
        $service = new LedgerService();

        $this->view('ledger/show', [
            'title' => 'Ledger',
            'vyaparis' => $vyaparis,
            'vyapariId' => $vyapariId,
            'start' => $start,
            'end' => $end,
            'opening' => $start ? $service->totals($vyapariId, null, date('Y-m-d', strtotime($start . ' -1 day'))) : ['ntwt' => 0, 'cash' => 0, 'rtgs' => 0, 'cat_995' => 0, 'gold_999' => 0],
            'closing' => $service->totals($vyapariId, null, $end),
            'liya' => $service->sideTotals($vyapariId, 'liya', $start, $end),
            'diya' => $service->sideTotals($vyapariId, 'diya', $start, $end),
            'transactions' => (new LedgerTransaction())->search(['vyapari_id' => $vyapariId, 'start_date' => $start, 'end_date' => $end]),
        ]);
    }
}
