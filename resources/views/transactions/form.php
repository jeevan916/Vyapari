<?php use function App\Core\csrf_field; use function App\Core\e; use function App\Core\url; ?>
<?php
$isEdit = !empty($transaction['id']);
$type = $transaction['transaction_type'] ?? 'maal-liya';
$systemNames = [
    'fine-diya' => 'FINE DIYA',
    'cash-diya' => 'CASH PAID',
    'bank-payment-diya' => 'RTGS DONE',
    'cash-rate-cut' => 'CASH CUT FINE JAMA',
    'bill-rate-cut' => 'BILL CUT FINE JAMA',
];
$productName = $transaction['product_name'] ?? ($systemNames[$type] ?? '');
$isMoneyOnly = in_array($type, ['cash-diya', 'bank-payment-diya'], true);
$isFineOnly = $type === 'fine-diya';
$isRateCut = in_array($type, ['cash-rate-cut', 'bill-rate-cut'], true);
$showProduct = in_array($type, ['maal-liya', 'maal-return'], true);
$showCashInputs = in_array($type, ['maal-liya', 'maal-return', 'cash-diya', 'cash-rate-cut'], true);
$showBillInputs = in_array($type, ['maal-liya', 'maal-return', 'bank-payment-diya', 'bill-rate-cut'], true);
?>
<div class="page-heading">
    <div><span class="eyebrow">BOOKKEEPING</span><h1><?= e($title) ?></h1></div>
</div>

<form class="panel transaction-form grid cols-3" method="post" action="<?= e(url($isEdit ? '/transactions/update' : '/transactions/store')) ?>">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= e($transaction['id']) ?>"><?php endif; ?>
    <input type="hidden" name="product_name" value="<?= e($productName) ?>">
    <?php if (!$showProduct): ?><input type="hidden" name="product_id" value=""><?php endif; ?>

    <label>Transaction Date <input type="date" name="transaction_date" required value="<?= e($transaction['transaction_date'] ?? date('Y-m-d')) ?>"></label>
    <label>Vyapari <select name="vyapari_id" required><option value="">Select Vyapari</option><?php foreach ($vyaparis as $v): ?><option value="<?= e($v['id']) ?>" <?= (($transaction['vyapari_id'] ?? '') == $v['id']) ? 'selected' : '' ?>><?= e($v['vyapari_name']) ?></option><?php endforeach; ?></select></label>
    <label>Type <select name="transaction_type" required data-transaction-type><?php foreach ($types as $key => $label): ?><option value="<?= e($key) ?>" <?= ($type === $key) ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>

    <?php if ($showProduct): ?>
        <label>Product <select name="product_id" data-product-select><option value="">Select Product</option><?php foreach ($products as $p): ?><option value="<?= e($p['id']) ?>" data-name="<?= e($p['product_name']) ?>" data-rate="<?= e($p['rate']) ?>" data-purity="<?= e($p['purity']) ?>" <?= (($transaction['product_id'] ?? '') == $p['id']) ? 'selected' : '' ?>><?= e($p['product_name']) ?><?= !empty($p['vyapari_name']) ? ' - ' . e($p['vyapari_name']) : '' ?></option><?php endforeach; ?></select></label>
        <label>Product Name <input name="product_name_visible" data-product-name-visible value="<?= e($productName) ?>" readonly></label>
    <?php else: ?>
        <label>Product Name <input value="<?= e($productName) ?>" readonly></label>
    <?php endif; ?>

    <?php if (!$isMoneyOnly): ?>
        <label>Melting <input type="number" step="any" name="melting" value="<?= e($transaction['melting'] ?? '') ?>"></label>
        <?php if (in_array($type, ['maal-liya', 'maal-return'], true)): ?>
            <label>Wstg <input type="number" step="any" name="rate" value="<?= e($transaction['rate'] ?? '') ?>"></label>
        <?php else: ?>
            <input type="hidden" name="rate" value="0">
        <?php endif; ?>
        <label>Gr.Wt <input type="number" step="any" name="gross_weight" data-gross-weight value="<?= e($transaction['gross_weight'] ?? '') ?>"></label>
        <label>Net.Wt <input type="number" step="any" name="net_weight" data-net-weight value="<?= e($transaction['net_weight'] ?? '') ?>"></label>
    <?php else: ?>
        <input type="hidden" name="melting" value="0"><input type="hidden" name="rate" value="0"><input type="hidden" name="gross_weight" value="0"><input type="hidden" name="net_weight" value="0">
    <?php endif; ?>

    <?php if ($showCashInputs): ?>
        <label><?= $type === 'cash-diya' ? 'Cash Amount Paid' : 'Cash Charge Amount' ?><input type="number" step="any" name="cash_charge_amount" value="<?= e($transaction['cash_charge_amount'] ?? '0') ?>"></label>
    <?php else: ?>
        <input type="hidden" name="cash_charge_amount" value="0">
    <?php endif; ?>

    <?php if ($showBillInputs): ?>
        <label><?= $type === 'bank-payment-diya' ? 'RTGS/CHEQUE Amount Paid' : 'Bill Charge Amount' ?><input type="number" step="any" name="bill_charge_amount" value="<?= e($transaction['bill_charge_amount'] ?? '0') ?>"></label>
    <?php else: ?>
        <input type="hidden" name="bill_charge_amount" value="0">
    <?php endif; ?>

    <?php if (in_array($type, ['maal-liya', 'maal-return', 'bill-rate-cut'], true)): ?>
        <label>10gm Bill 995 Rate <input type="number" step="any" name="ten_gram_bill_995_rate" value="<?= e($transaction['ten_gram_bill_995_rate'] ?? '') ?>"></label>
    <?php else: ?>
        <input type="hidden" name="ten_gram_bill_995_rate" value="0">
    <?php endif; ?>

    <?php if (in_array($type, ['maal-liya', 'maal-return', 'cash-rate-cut'], true)): ?>
        <label>10gm Cash 995 Rate <input type="number" step="any" name="ten_gram_cash_995_rate" value="<?= e($transaction['ten_gram_cash_995_rate'] ?? '') ?>"></label>
    <?php else: ?>
        <input type="hidden" name="ten_gram_cash_995_rate" value="0">
    <?php endif; ?>

    <?php if ($type === 'opening-balance'): ?>
        <label>Liya/Diya <select name="liya_diya"><option value="liya" <?= (($transaction['liya_diya'] ?? '') === 'liya') ? 'selected' : '' ?>>Liya</option><option value="diya" <?= (($transaction['liya_diya'] ?? '') === 'diya') ? 'selected' : '' ?>>Diya</option></select></label>
        <label>Opening Cash Amount <input type="number" step="any" name="cash_amount" value="<?= e($transaction['cash_amount'] ?? '0') ?>"></label>
        <label>Opening RTGS Amount <input type="number" step="any" name="rtgs_amount" value="<?= e($transaction['rtgs_amount'] ?? '0') ?>"></label>
        <label>Opening Cat 995 <input type="number" step="any" name="balance_cat_995" value="<?= e($transaction['balance_cat_995'] ?? '0') ?>"></label>
    <?php endif; ?>

    <label style="grid-column:1/-1">Notes <textarea name="notes"><?= e($transaction['notes'] ?? '') ?></textarea></label>
    <div class="calc-note" style="grid-column:1/-1">
        <?php if ($isRateCut): ?>Rate cut saves two ledger rows, matching Yii: fine diya plus cash/bill jama.<?php endif; ?>
        <?php if ($isFineOnly): ?>Fine diya stores rate and money fields as zero, with gold balance negative.<?php endif; ?>
        <?php if ($isMoneyOnly): ?>This entry affects only <?= $type === 'cash-diya' ? 'cash' : 'RTGS' ?> balance.<?php endif; ?>
    </div>
    <div class="form-actions"><button class="btn success">Save</button><a class="btn" href="<?= e(url('/ledger' . (!empty($transaction['vyapari_id']) ? '?vyapari_id=' . $transaction['vyapari_id'] : ''))) ?>">Close</a></div>
</form>
