<?php use App\Services\LedgerService; use function App\Core\e; use function App\Core\money; use function App\Core\url; use function App\Core\weight3; ?>
<?php
$balances = (new LedgerService())->dashboardTotals();
$byId = [];
foreach ($balances as $balance) { $byId[$balance['id']] = $balance; }
$totalCash = array_sum(array_column($balances, 'cash'));
$totalRtgs = array_sum(array_column($balances, 'rtgs'));
$totalGold = array_sum(array_column($balances, 'gold_999'));
?>
<div class="page-heading">
    <div><span class="eyebrow">DIRECTORY</span><h1>Vyaparis</h1></div>
    <a class="btn success" href="<?= e(url('/vyaparis/create')) ?>">Add Vyapari</a>
</div>

<section class="directory-summary">
    <div><span>Cash</span><strong>₹ <?= money($totalCash) ?></strong></div>
    <div><span>RTGS</span><strong>₹ <?= money($totalRtgs) ?></strong></div>
    <div class="directory-gold"><span>Gold · 999</span><strong><?= weight3($totalGold) ?> g</strong></div>
</section>

<form class="directory-search-form" method="get" action="<?= e(url('/vyaparis')) ?>">
    <input name="q" placeholder="Search Vyapari or company name..." value="<?= e($_GET['q'] ?? '') ?>">
    <button class="btn primary">Search</button>
    <a class="btn link" href="<?= e(url('/vyaparis')) ?>">Clear</a>
</form>

<section class="directory-table-wrap">
    <table class="directory-table">
        <thead><tr><th>Trader</th><th>Cash</th><th>RTGS</th><th>Gold · 999</th><th>Open</th></tr></thead>
        <tbody>
        <?php foreach ($vyaparis as $row): ?>
            <?php $balance = $byId[$row['id']] ?? ['cash' => 0, 'rtgs' => 0, 'gold_999' => 0]; ?>
            <tr>
                <td data-label="Trader"><strong><?= e($row['vyapari_name']) ?></strong><small><?= e($row['company_name'] ?: $row['primary_number'] ?: $row['city']) ?></small></td>
                <td data-label="Cash">₹ <?= money($balance['cash']) ?></td>
                <td data-label="RTGS">₹ <?= money($balance['rtgs']) ?></td>
                <td data-label="Gold · 999" class="directory-gold-value"><?= weight3($balance['gold_999']) ?> g</td>
                <td data-label="Open" class="directory-actions">
                    <a class="directory-action primary" href="<?= e(url('/ledger?vyapari_id=' . $row['id'])) ?>">Ledger</a>
                    <a class="directory-action" href="<?= e(url('/vyaparis/edit?id=' . $row['id'])) ?>">Edit</a>
                    <a class="directory-action" href="<?= e(url('/products/create?vyapari_id=' . $row['id'])) ?>">+ Product</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
