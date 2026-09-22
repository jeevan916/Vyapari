<?php use App\Models\LedgerTransaction; use function App\Core\csrf_field; use function App\Core\e; use function App\Core\money; use function App\Core\url; use function App\Core\weight3; ?>
<div class="topbar"><h1>Transactions</h1><a class="btn primary" href="<?= e(url('/transactions/create')) ?>">New Transaction</a></div>
<form class="panel grid cols-3" method="get" action="<?= e(url('/transactions')) ?>">
    <label>Vyapari <select name="vyapari_id"><option value="">All</option><?php foreach ($vyaparis as $v): ?><option value="<?= e($v['id']) ?>" <?= (($filters['vyapari_id'] ?? '') == $v['id']) ? 'selected' : '' ?>><?= e($v['vyapari_name']) ?></option><?php endforeach; ?></select></label>
    <label>Start <input type="date" name="start_date" value="<?= e($filters['start_date'] ?? '') ?>"></label>
    <label>End <input type="date" name="end_date" value="<?= e($filters['end_date'] ?? '') ?>"></label>
    <div class="form-actions"><button class="btn">Filter</button></div>
</form>
<section class="table-wrap" style="margin-top:18px">
    <table>
        <thead><tr><th>Date</th><th>Vyapari</th><th>Type</th><th>Product</th><th class="num">Net Wt</th><th class="num">Cash</th><th class="num">RTGS</th><th class="num">Cat 995</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($transactions as $row): ?>
            <tr>
                <td><?= e($row['transaction_date']) ?></td><td><?= e($row['vyapari_name']) ?></td><td><?= e(LedgerTransaction::TYPES[$row['transaction_type']] ?? $row['transaction_type']) ?></td><td><?= e($row['product_name']) ?></td>
                <td class="num"><?= weight3($row['net_weight']) ?></td><td class="num"><?= money($row['cash_amount']) ?></td><td class="num"><?= money($row['rtgs_amount']) ?></td><td class="num"><?= weight3($row['balance_cat_995']) ?></td>
                <td class="actions"><a class="btn" href="<?= e(url('/transactions/edit?id=' . $row['id'])) ?>">Edit</a><form method="post" action="<?= e(url('/transactions/delete')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($row['id']) ?>"><button class="btn danger">Delete</button></form></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
