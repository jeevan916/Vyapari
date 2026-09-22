<?php use function App\Core\csrf_field; use function App\Core\e; use function App\Core\url; ?>
<?php $isEdit = !empty($transaction['id']); ?>
<div class="topbar"><h1><?= e($title) ?></h1></div>
<form class="panel grid cols-3" method="post" action="<?= e(url($isEdit ? '/transactions/update' : '/transactions/store')) ?>">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= e($transaction['id']) ?>"><?php endif; ?>
    <label>Vyapari <select name="vyapari_id" required><option value="">Select</option><?php foreach ($vyaparis as $v): ?><option value="<?= e($v['id']) ?>" <?= (($transaction['vyapari_id'] ?? '') == $v['id']) ? 'selected' : '' ?>><?= e($v['vyapari_name']) ?></option><?php endforeach; ?></select></label>
    <label>Type <select name="transaction_type" required><?php foreach ($types as $key => $label): ?><option value="<?= e($key) ?>" <?= (($transaction['transaction_type'] ?? '') === $key) ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
    <label>Date <input type="date" name="transaction_date" required value="<?= e($transaction['transaction_date'] ?? date('Y-m-d')) ?>"></label>
    <label>Product <select name="product_id" data-product-select><option value="">Manual</option><?php foreach ($products as $p): ?><option value="<?= e($p['id']) ?>" data-name="<?= e($p['product_name']) ?>" data-rate="<?= e($p['rate']) ?>" data-purity="<?= e($p['purity']) ?>" <?= (($transaction['product_id'] ?? '') == $p['id']) ? 'selected' : '' ?>><?= e($p['product_name']) ?><?= !empty($p['vyapari_name']) ? ' - ' . e($p['vyapari_name']) : '' ?></option><?php endforeach; ?></select></label>
    <label>Product Name <input name="product_name" required value="<?= e($transaction['product_name'] ?? '') ?>"></label>
    <label>Liya/Diya for Opening <select name="liya_diya"><option value="liya" <?= (($transaction['liya_diya'] ?? '') === 'liya') ? 'selected' : '' ?>>Liya</option><option value="diya" <?= (($transaction['liya_diya'] ?? '') === 'diya') ? 'selected' : '' ?>>Diya</option></select></label>
    <label>Gross Weight <input type="number" step="0.001" name="gross_weight" value="<?= e($transaction['gross_weight'] ?? '0') ?>"></label>
    <label>Net Weight <input type="number" step="0.001" name="net_weight" value="<?= e($transaction['net_weight'] ?? '0') ?>"></label>
    <label>Melting <input type="number" step="0.001" name="melting" value="<?= e($transaction['melting'] ?? '0') ?>"></label>
    <label>Wstg/Rate <input type="number" step="0.001" name="rate" value="<?= e($transaction['rate'] ?? '0') ?>"></label>
    <label>Cash Charge <input type="number" step="0.01" name="cash_charge_amount" value="<?= e($transaction['cash_charge_amount'] ?? '0') ?>"></label>
    <label>Bill Charge <input type="number" step="0.01" name="bill_charge_amount" value="<?= e($transaction['bill_charge_amount'] ?? '0') ?>"></label>
    <label>10gm Bill 995 Rate <input type="number" step="0.01" name="ten_gram_bill_995_rate" value="<?= e($transaction['ten_gram_bill_995_rate'] ?? '0') ?>"></label>
    <label>10gm Cash 995 Rate <input type="number" step="0.01" name="ten_gram_cash_995_rate" value="<?= e($transaction['ten_gram_cash_995_rate'] ?? '0') ?>"></label>
    <label>Opening Cash Amount <input type="number" step="0.01" name="cash_amount" value="<?= e($transaction['cash_amount'] ?? '0') ?>"></label>
    <label>Opening RTGS Amount <input type="number" step="0.01" name="rtgs_amount" value="<?= e($transaction['rtgs_amount'] ?? '0') ?>"></label>
    <label>Opening Cat 995 <input type="number" step="0.001" name="balance_cat_995" value="<?= e($transaction['balance_cat_995'] ?? '0') ?>"></label>
    <label style="grid-column:1/-1">Notes <textarea name="notes"><?= e($transaction['notes'] ?? '') ?></textarea></label>
    <div class="form-actions"><button class="btn primary">Save</button><a class="btn" href="<?= e(url('/transactions')) ?>">Cancel</a></div>
</form>
