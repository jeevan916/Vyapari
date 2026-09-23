<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\LedgerTransaction;
use App\Models\Product;
use App\Models\Vyapari;
use function App\Core\flash;

final class TransactionController extends Controller
{
    private $transactions;
    private const SYSTEM_PRODUCT_NAMES = [
        'fine-diya' => 'FINE DIYA',
        'cash-diya' => 'CASH PAID',
        'bank-payment-diya' => 'RTGS DONE',
        'cash-rate-cut' => 'CASH CUT FINE JAMA',
        'bill-rate-cut' => 'BILL CUT FINE JAMA',
    ];

    public function __construct()
    {
        $this->transactions = new LedgerTransaction();
    }

    public function index(): void
    {
        Auth::requireLogin();
        $filters = [
            'vyapari_id' => $_GET['vyapari_id'] ?? null,
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null,
        ];
        $this->view('transactions/index', [
            'title' => 'Transactions',
            'transactions' => $this->transactions->search($filters),
            'vyaparis' => (new Vyapari())->all('vyapari_name ASC'),
            'filters' => $filters,
        ]);
    }

    public function create(): void
    {
        Auth::requireLogin();
        $this->view('transactions/form', $this->formData('New Transaction', [
            'transaction_type' => $_GET['type'] ?? 'maal-liya',
            'vyapari_id' => $_GET['vyapari_id'] ?? '',
            'transaction_date' => date('Y-m-d'),
        ]));
    }

    public function store(): void
    {
        Auth::requireLogin();
        $payload = $this->calculatedPayload($_POST);
        if (in_array($payload['transaction_type'], ['cash-rate-cut', 'bill-rate-cut'], true)) {
            $this->transactions->create(array_merge($payload, [
                'liya_diya' => 'diya',
                'gross_weight' => -abs((float) $payload['gross_weight']),
                'net_weight' => -abs((float) $payload['net_weight']),
                'cash_charge_amount' => 0,
                'bill_charge_amount' => 0,
                'ten_gram_bill_995_rate' => 0,
                'ten_gram_cash_995_rate' => 0,
                'gst_bhav' => 0,
                'cash_bhav' => 0,
                'cash_amount' => 0,
                'rtgs_amount' => 0,
            ]));
            $payload['liya_diya'] = 'liya';
            $payload['net_weight'] = 0;
            $payload['melting'] = 0;
            $payload['balance_cat_995'] = 0;
        }
        $this->transactions->create($payload);
        flash('success', 'Transaction saved.');
        $this->redirect('/transactions');
    }

    public function edit(): void
    {
        Auth::requireLogin();
        $this->view('transactions/form', $this->formData('Edit Transaction', $this->transactions->find((int) $_GET['id'])));
    }

    public function update(): void
    {
        Auth::requireLogin();
        $this->transactions->update((int) $_POST['id'], $this->calculatedPayload($_POST));
        flash('success', 'Transaction updated.');
        $this->redirect('/transactions');
    }

    public function delete(): void
    {
        Auth::requireLogin();
        $this->transactions->delete((int) $_POST['id']);
        flash('success', 'Transaction deleted.');
        $this->redirect('/transactions');
    }

    private function formData(string $title, ?array $transaction): array
    {
        return [
            'title' => $title,
            'transaction' => $transaction ?? [],
            'vyaparis' => (new Vyapari())->all('vyapari_name ASC'),
            'products' => (new Product())->withVyapari(),
            'types' => LedgerTransaction::TYPES,
        ];
    }

    private function calculatedPayload(array $input): array
    {
        $type = $input['transaction_type'] ?? 'maal-liya';
        $input = $this->applyYiiDefaults($type, $input);
        $net = (float) ($input['net_weight'] ?? 0);
        $gross = (float) ($input['gross_weight'] ?? 0);
        $melting = (float) ($input['melting'] ?? 0);
        $rate = in_array($type, ['fine-diya', 'cash-rate-cut', 'bill-rate-cut'], true) ? 0.0 : (float) ($input['rate'] ?? 0);
        $cashCharge = (float) ($input['cash_charge_amount'] ?? 0);
        $billCharge = (float) ($input['bill_charge_amount'] ?? 0);
        $bill995 = (float) ($input['ten_gram_bill_995_rate'] ?? 0);
        $cash995 = (float) ($input['ten_gram_cash_995_rate'] ?? 0);

        $purity999 = (($melting + $rate) * $net / 100);
        $purity995 = $purity999 / 0.995;
        $gstBhav = ($bill995 / 10) + ((($bill995 / 100) * 3) / 10);
        $cashBhav = $cash995 / 10;
        $cashAmount = 0.0;
        $rtgsAmount = 0.0;
        $balanceCat995 = 0.0;
        $side = 'liya';

        if ($type === 'maal-liya') {
            $cashAmount = ($cashBhav * $purity995) + $cashCharge;
            $rtgsAmount = ($gstBhav * $purity995) + $billCharge;
            $balanceCat995 = (($cashAmount + $rtgsAmount) != 0.0) ? 0.0 : $purity995;
        } elseif ($type === 'maal-return') {
            $side = 'diya';
            $gross = -abs($gross);
            $net = -abs($net);
            $purity999 = -abs($purity999);
            $purity995 = -abs($purity995);
            $cashAmount = -abs(($cashBhav * abs($purity995)) + $cashCharge);
            $rtgsAmount = -abs(($gstBhav * abs($purity995)) + $billCharge);
            $balanceCat995 = (($cashAmount + $rtgsAmount) != 0.0) ? 0.0 : $purity995;
        } elseif ($type === 'cash-diya') {
            $side = 'diya';
            $cashAmount = -abs($cashCharge);
        } elseif ($type === 'bank-payment-diya') {
            $side = 'diya';
            $rtgsAmount = -abs($billCharge);
        } elseif ($type === 'fine-diya') {
            $side = 'diya';
            $gross = -abs($gross);
            $net = -abs($net);
            $purity999 = -abs($purity999);
            $purity995 = -abs($purity995);
            $balanceCat995 = $purity995;
        } elseif ($type === 'cash-rate-cut') {
            $cashAmount = ($cashBhav * $purity995) + $cashCharge;
            $balanceCat995 = -abs($purity995);
        } elseif ($type === 'bill-rate-cut') {
            $rtgsAmount = ($gstBhav * $purity995) + $billCharge;
            $balanceCat995 = -abs($purity995);
        } elseif ($type === 'opening-balance') {
            $side = ($input['liya_diya'] ?? 'liya') === 'diya' ? 'diya' : 'liya';
            $cashAmount = (float) ($input['cash_amount'] ?? 0);
            $rtgsAmount = (float) ($input['rtgs_amount'] ?? 0);
            $balanceCat995 = (float) ($input['balance_cat_995'] ?? 0);
        }

        return [
            'vyapari_id' => (int) ($input['vyapari_id'] ?? 0),
            'product_id' => ($input['product_id'] ?? '') !== '' ? (int) $input['product_id'] : null,
            'transaction_type' => $type,
            'liya_diya' => $side,
            'product_name' => trim($input['product_name'] ?? self::SYSTEM_PRODUCT_NAMES[$type] ?? LedgerTransaction::TYPES[$type] ?? $type),
            'gross_weight' => round($gross, 3),
            'net_weight' => round($net, 3),
            'melting' => round($melting, 3),
            'rate' => round($rate, 3),
            'cash_charge_amount' => round($cashCharge, 2),
            'bill_charge_amount' => round($billCharge, 2),
            'ten_gram_bill_995_rate' => round($bill995, 2),
            'ten_gram_cash_995_rate' => round($cash995, 2),
            'purity_999' => round($purity999, 3),
            'purity_995' => round($purity995, 3),
            'gst_bhav' => round($gstBhav, 2),
            'cash_bhav' => round($cashBhav, 2),
            'cash_amount' => round($cashAmount, 2),
            'rtgs_amount' => round($rtgsAmount, 2),
            'balance_cat_995' => round($balanceCat995, 3),
            'transaction_date' => $input['transaction_date'] ?: date('Y-m-d'),
            'notes' => trim($input['notes'] ?? ''),
        ];
    }

    private function applyYiiDefaults(string $type, array $input): array
    {
        if (isset(self::SYSTEM_PRODUCT_NAMES[$type]) && trim((string) ($input['product_name'] ?? '')) === '') {
            $input['product_name'] = self::SYSTEM_PRODUCT_NAMES[$type];
        }

        if (in_array($type, ['fine-diya', 'cash-diya', 'bank-payment-diya', 'cash-rate-cut', 'bill-rate-cut'], true)) {
            $input['product_id'] = '';
        }

        $zeroByType = [
            'cash-diya' => ['bill_charge_amount', 'ten_gram_bill_995_rate', 'ten_gram_cash_995_rate', 'rate', 'gross_weight', 'net_weight', 'melting'],
            'bank-payment-diya' => ['gross_weight', 'net_weight', 'melting', 'rate', 'cash_charge_amount', 'ten_gram_bill_995_rate', 'ten_gram_cash_995_rate'],
            'fine-diya' => ['rate', 'cash_charge_amount', 'bill_charge_amount', 'ten_gram_bill_995_rate', 'ten_gram_cash_995_rate'],
            'cash-rate-cut' => ['rate', 'bill_charge_amount', 'ten_gram_bill_995_rate'],
            'bill-rate-cut' => ['rate', 'cash_charge_amount', 'ten_gram_cash_995_rate'],
        ];

        foreach ($zeroByType[$type] ?? [] as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                $input[$field] = 0;
            }
        }

        if (!isset($input['product_name']) || trim((string) $input['product_name']) === '') {
            $input['product_name'] = LedgerTransaction::TYPES[$type] ?? $type;
        }

        return $input;
    }
}
