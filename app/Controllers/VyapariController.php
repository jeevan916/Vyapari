<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\LedgerTransaction;
use App\Models\Vyapari;
use function App\Core\flash;

final class VyapariController extends Controller
{
    private $vyaparis;

    public function __construct()
    {
        $this->vyaparis = new Vyapari();
    }

    public function index(): void
    {
        Auth::requireLogin();
        $this->view('vyaparis/index', ['title' => 'Vyaparis', 'vyaparis' => $this->vyaparis->search($_GET['q'] ?? null)]);
    }

    public function create(): void
    {
        Auth::requireLogin();
        $this->view('vyaparis/form', ['title' => 'New Vyapari', 'vyapari' => []]);
    }

    public function store(): void
    {
        Auth::requireLogin();
        $this->vyaparis->create($this->payload());
        flash('success', 'Vyapari saved.');
        $this->redirect('/vyaparis');
    }

    public function edit(): void
    {
        Auth::requireLogin();
        $this->view('vyaparis/form', ['title' => 'Edit Vyapari', 'vyapari' => $this->vyaparis->find((int) $_GET['id'])]);
    }

    public function update(): void
    {
        Auth::requireLogin();
        $this->vyaparis->update((int) $_POST['id'], $this->payload());
        flash('success', 'Vyapari updated.');
        $this->redirect('/vyaparis');
    }

    public function delete(): void
    {
        Auth::requireLogin();
        $this->vyaparis->delete((int) $_POST['id']);
        flash('success', 'Vyapari deleted.');
        $this->redirect('/vyaparis');
    }

    public function settle(): void
    {
        Auth::requireLogin();
        (new LedgerTransaction())->hardDeleteForVyapari((int) $_POST['vyapari_id']);
        flash('success', 'All transactions for this vyapari were settled.');
        $this->redirect('/ledger?vyapari_id=' . (int) $_POST['vyapari_id']);
    }

    public function roundoff(): void
    {
        Auth::requireLogin();
        $tx = new LedgerTransaction();
        $vyapariId = (int) $_POST['vyapari_id'];
        $date = $_POST['end_date'] ?: date('Y-m-d');
        $tx->deleteFromDate($vyapariId, $date);

        $entries = [
            ['cash_amount', (float) $_POST['grandtotal_cash'], 'CASH'],
            ['rtgs_amount', (float) $_POST['grandtotal_rtgs'], 'RTGS'],
            ['balance_cat_995', (float) $_POST['grandtotal_cat_995'], 'CAT_995'],
        ];
        foreach ($entries as [$field, $amount, $label]) {
            if ($amount == 0.0) {
                continue;
            }
            $tx->create(array_merge($this->zeroTransaction($vyapariId, $date), [
                'liya_diya' => $amount <= 0 ? 'diya' : 'liya',
                'product_name' => 'Roundoff carry forward ' . $label,
                $field => $amount,
            ]));
        }

        flash('success', 'Balances carried forward.');
        $this->redirect('/ledger?vyapari_id=' . $vyapariId . '&end_date=' . $date);
    }

    private function payload(): array
    {
        return [
            'vyapari_name' => trim($_POST['vyapari_name'] ?? ''),
            'company_name' => trim($_POST['company_name'] ?? ''),
            'email_id' => trim($_POST['email_id'] ?? ''),
            'primary_number' => trim($_POST['primary_number'] ?? ''),
            'secondary_number' => trim($_POST['secondary_number'] ?? ''),
            'gst_number' => trim($_POST['gst_number'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'state' => trim($_POST['state'] ?? ''),
        ];
    }

    private function zeroTransaction(int $vyapariId, string $date): array
    {
        return [
            'vyapari_id' => $vyapariId, 'product_id' => null, 'transaction_type' => 'opening-balance', 'liya_diya' => 'liya',
            'product_name' => 'Roundoff carry forward', 'gross_weight' => 0, 'net_weight' => 0, 'melting' => 0, 'rate' => 0,
            'cash_charge_amount' => 0, 'bill_charge_amount' => 0, 'ten_gram_bill_995_rate' => 0, 'ten_gram_cash_995_rate' => 0,
            'purity_999' => 0, 'purity_995' => 0, 'gst_bhav' => 0, 'cash_bhav' => 0, 'cash_amount' => 0, 'rtgs_amount' => 0,
            'balance_cat_995' => 0, 'transaction_date' => $date, 'notes' => null,
        ];
    }
}
