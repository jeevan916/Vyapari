<?php use App\Models\LedgerTransaction; use function App\Core\csrf_field; use function App\Core\e; use function App\Core\money; use function App\Core\url; use function App\Core\weight3; ?>
<div class="topbar">
    <h1>Ledger</h1>
    <div class="actions no-print"><button class="btn" onclick="window.print()">Print</button><a class="btn primary" href="<?= e(url('/transactions/create')) ?>">New Transaction</a></div>
</div>
<form class="panel grid cols-3 no-print" method="get" action="<?= e(url('/ledger')) ?>">
    <label>Vyapari <select name="vyapari_id"><?php foreach ($vyaparis as $v): ?><option value="<?= e($v['id']) ?>" <?= ($vyapariId == $v['id']) ? 'selected' : '' ?>><?= e($v['vyapari_name']) ?></option><?php endforeach; ?></select></label>
    <label>Start <input type="date" name="start_date" value="<?= e($start ?? '') ?>"></label>
    <label>End <input type="date" name="end_date" value="<?= e($end ?? '') ?>"></label>
    <div class="form-actions"><button class="btn">Open</button></div>
</form>
<section class="grid cols-3" style="margin-top:18px">
    <div class="stat"><span>Opening Gold 999</span><strong><?= weight3($opening['gold_999']) ?></strong></div>
    <div class="stat"><span>Closing Cash</span><strong><?= money($closing['cash']) ?></strong></div>
    <div class="stat"><span>Closing RTGS</span><strong><?= money($closing['rtgs']) ?></strong></div>
</section>
<section class="grid cols-2" style="margin-top:18px">
    <div class="panel"><h2>Liya Total</h2><p>Net: <?= weight3($liya['ntwt']) ?> | Cash: <?= money($liya['cash']) ?> | RTGS: <?= money($liya['rtgs']) ?> | Cat 995: <?= weight3($liya['cat_995']) ?></p></div>
    <div class="panel"><h2>Diya Total</h2><p>Net: <?= weight3($diya['ntwt']) ?> | Cash: <?= money($diya['cash']) ?> | RTGS: <?= money($diya['rtgs']) ?> | Cat 995: <?= weight3($diya['cat_995']) ?></p></div>
</section>
<section class="panel no-print" style="margin-top:18px">
    <form class="actions" method="post" action="<?= e(url('/vyaparis/roundoff')) ?>">
        <?= csrf_field() ?><input type="hidden" name="vyapari_id" value="<?= e($vyapariId) ?>"><input type="hidden" name="end_date" value="<?= e($end) ?>">
        <input type="hidden" name="grandtotal_cash" value="<?= e($closing['cash']) ?>"><input type="hidden" name="grandtotal_rtgs" value="<?= e($closing['rtgs']) ?>"><input type="hidden" name="grandtotal_cat_995" value="<?= e($closing['cat_995']) ?>">
        <button class="btn">Roundoff Carry Forward</button>
    </form>
    <form class="actions" method="post" action="<?= e(url('/vyaparis/settle')) ?>" style="margin-top:8px">
        <?= csrf_field() ?><input type="hidden" name="vyapari_id" value="<?= e($vyapariId) ?>"><button class="btn danger">Settle All Transactions</button>
    </form>
</section>
<section class="table-wrap" style="margin-top:18px">
    <table>
        <thead><tr><th>Date</th><th>Side</th><th>Type</th><th>Product</th><th class="num">Net</th><th class="num">Cash</th><th class="num">RTGS</th><th class="num">Cat 995</th><th class="num">Gold 999</th></tr></thead>
        <tbody>
        <?php foreach ($transactions as $row): ?>
            <tr>
                <td><?= e($row['transaction_date']) ?></td><td><?= e(ucfirst($row['liya_diya'])) ?></td><td><?= e(LedgerTransaction::TYPES[$row['transaction_type']] ?? $row['transaction_type']) ?></td><td><?= e($row['product_name']) ?></td>
                <td class="num"><?= weight3($row['net_weight']) ?></td><td class="num"><?= money($row['cash_amount']) ?></td><td class="num"><?= money($row['rtgs_amount']) ?></td><td class="num"><?= weight3($row['balance_cat_995']) ?></td><td class="num"><?= weight3(((float) $row['balance_cat_995']) * 0.995) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
