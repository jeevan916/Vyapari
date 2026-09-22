<?php use function App\Core\e; use function App\Core\money; use function App\Core\url; use function App\Core\weight3; ?>
<div class="topbar">
    <h1>Dashboard</h1>
    <div class="actions">
        <a class="btn primary" href="<?= e(url('/transactions/create')) ?>">New Transaction</a>
        <a class="btn" href="<?= e(url('/ledger')) ?>">Open Ledger</a>
    </div>
</div>
<?php
$cash = array_sum(array_column($rows, 'cash'));
$rtgs = array_sum(array_column($rows, 'rtgs'));
$gold = array_sum(array_column($rows, 'gold_999'));
?>
<section class="grid cols-3">
    <div class="stat"><span>Total Cash</span><strong><?= money($cash) ?></strong></div>
    <div class="stat"><span>Total RTGS</span><strong><?= money($rtgs) ?></strong></div>
    <div class="stat"><span>Total Gold 999</span><strong><?= weight3($gold) ?></strong></div>
</section>
<section class="table-wrap" style="margin-top:18px">
    <table>
        <thead><tr><th>Vyapari</th><th>Company</th><th class="num">Cash</th><th class="num">RTGS</th><th class="num">Gold 999</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e($row['vyapari_name']) ?></td>
                <td><?= e($row['company_name']) ?></td>
                <td class="num"><?= money($row['cash']) ?></td>
                <td class="num"><?= money($row['rtgs']) ?></td>
                <td class="num"><?= weight3($row['gold_999']) ?></td>
                <td><a class="btn" href="<?= e(url('/ledger?vyapari_id=' . $row['id'])) ?>">Ledger</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
