<?php use App\Models\LedgerTransaction; use function App\Core\csrf_field; use function App\Core\e; use function App\Core\money; use function App\Core\url; use function App\Core\weight3; ?>
<?php
$currentVyapari = null;
foreach ($vyaparis as $v) { if ((int) $v['id'] === (int) $vyapariId) { $currentVyapari = $v; break; } }
$styles = [
    'maal-liya' => ['class' => 'type-maal-liya', 'name' => 'Maal liya'],
    'maal-return' => ['class' => 'type-maal-return', 'name' => 'Maal return'],
    'fine-diya' => ['class' => 'type-fine-diya', 'name' => 'Fine diya'],
    'cash-diya' => ['class' => 'type-cash-diya', 'name' => 'Cash diya'],
    'bank-payment-diya' => ['class' => 'type-bank-payment-diya', 'name' => 'Bank payment'],
    'cash-rate-cut' => ['class' => 'type-cash-rate-cut', 'name' => 'Cash rate cut'],
    'bill-rate-cut' => ['class' => 'type-bill-rate-cut', 'name' => 'Bill rate cut'],
    'opening-balance' => ['class' => 'type-other', 'name' => 'Opening balance'],
];
$quick = [
    ['Maal liya', 'maal-liya', 'primary'],
    ['Maal return', 'maal-return', ''],
    ['Fine diya', 'fine-diya', 'warning'],
    ['Cash diya', 'cash-diya', 'success'],
    ['Bank payment', 'bank-payment-diya', 'danger'],
    ['Cash rate cut', 'cash-rate-cut', ''],
    ['Bill rate cut', 'bill-rate-cut', ''],
];
?>
<main class="mobile-ledger">
    <header class="ledger-header">
        <div class="ledger-controls">
            <a class="ledger-back" href="<?= e(url('/vyaparis')) ?>">‹ Traders</a>
            <button class="ledger-print" onclick="window.print()">Ledger view</button>
        </div>
        <h1><?= e($currentVyapari['vyapari_name'] ?? 'Ledger') ?></h1>
        <p><?= e($currentVyapari['company_name'] ?? '') ?></p>
    </header>

    <section class="ledger-filter card">
        <form class="compact-filter" method="get" action="<?= e(url('/ledger')) ?>">
            <input type="hidden" name="vyapari_id" value="<?= e($vyapariId) ?>">
            <label>From <input type="date" name="start_date" value="<?= e($start ?? '') ?>"></label>
            <label>To <input type="date" name="end_date" value="<?= e($end ?? '') ?>"></label>
            <div class="compact-filter-submit"><button class="btn primary">Apply</button></div>
        </form>
    </section>

    <section class="balance-grid">
        <article class="balance-card balance-gold"><span>Gold balance</span><strong><?= weight3($closing['gold_999']) ?> g</strong><small>999 purity</small></article>
        <article class="balance-card"><span>Cash balance</span><strong>₹ <?= money($closing['cash']) ?></strong></article>
        <article class="balance-card"><span>RTGS balance</span><strong>₹ <?= money($closing['rtgs']) ?></strong></article>
    </section>

    <section class="quick-actions">
        <h2>Add transaction</h2>
        <div class="quick-action-grid">
            <?php foreach ($quick as [$label, $type, $class]): ?>
                <a class="quick-action <?= e($class) ?>" href="<?= e(url('/transactions/create?vyapari_id=' . $vyapariId . '&type=' . $type)) ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
            <a class="quick-action product" href="<?= e(url('/products/create?vyapari_id=' . $vyapariId)) ?>">Add product</a>
        </div>
    </section>

    <section class="ledger-period card">
        <div><span>Opening gold · 999</span><strong><?= weight3($opening['gold_999']) ?> g</strong></div>
        <div><span>Received · 999</span><strong><?= weight3($liya['gold_999']) ?> g</strong></div>
        <div><span>Given · 999</span><strong><?= weight3($diya['gold_999']) ?> g</strong></div>
    </section>

    <section class="entry-list">
        <h2>Transactions</h2>
        <?php if (empty($transactions)): ?><div class="empty-ledger">No transactions for this period.</div><?php endif; ?>
        <?php foreach ($transactions as $row): ?>
            <?php $style = $styles[$row['transaction_type']] ?? ['class' => 'type-other', 'name' => 'Transaction']; $gold999 = ((float) $row['balance_cat_995']) * 0.995; ?>
            <article class="ledger-entry <?= e($style['class']) ?>">
                <div class="entry-date"><?= e(date('d M', strtotime($row['transaction_date']))) ?><small><?= e(date('Y', strtotime($row['transaction_date']))) ?></small></div>
                <span class="entry-type-dot"></span>
                <div class="entry-compact-details">
                    <span><?= e($row['product_name']) ?></span>
                    <?php if ((float) $row['net_weight'] !== 0.0): ?><span><?= weight3($row['net_weight']) ?>g</span><?php endif; ?>
                    <?php if ((float) $row['melting'] !== 0.0): ?><span><?= e($row['melting']) ?>%</span><?php endif; ?>
                    <?php if ((float) $row['rate'] !== 0.0): ?><span><?= e($row['rate']) ?>%</span><?php endif; ?>
                    <?php if ((float) $row['cash_bhav'] !== 0.0): ?><span>₹<?= money($row['cash_bhav']) ?></span><?php endif; ?>
                    <?php if ((float) $row['gst_bhav'] !== 0.0): ?><span>₹<?= money($row['gst_bhav']) ?></span><?php endif; ?>
                </div>
                <div class="entry-values">
                    <?php if ((float) $row['balance_cat_995'] !== 0.0): ?><strong><?= weight3($gold999) ?> g <small>999</small></strong><?php endif; ?>
                    <?php if ((float) $row['cash_amount'] !== 0.0): ?><span>Cash ₹ <?= money($row['cash_amount']) ?></span><?php endif; ?>
                    <?php if ((float) $row['rtgs_amount'] !== 0.0): ?><span>RTGS ₹ <?= money($row['rtgs_amount']) ?></span><?php endif; ?>
                </div>
                <details class="entry-accordion">
                    <summary></summary>
                    <div class="entry-accordion-body">
                        <div class="entry-detail-grid">
                            <div><span>Type</span><strong><?= e($style['name']) ?></strong></div>
                            <div><span>Gross</span><strong><?= weight3($row['gross_weight']) ?> g</strong></div>
                            <div><span>Net</span><strong><?= weight3($row['net_weight']) ?> g</strong></div>
                            <div><span>Melting</span><strong><?= e($row['melting']) ?>%</strong></div>
                            <div><span>Wstg</span><strong><?= e($row['rate']) ?>%</strong></div>
                            <div><span>Gold 995</span><strong><?= weight3($row['balance_cat_995']) ?> g</strong></div>
                            <?php if ($row['notes']): ?><div class="entry-detail-note"><span>Notes</span><strong><?= e($row['notes']) ?></strong></div><?php endif; ?>
                        </div>
                        <div class="entry-actions">
                            <a class="entry-action edit" href="<?= e(url('/transactions/edit?id=' . $row['id'])) ?>">Edit</a>
                            <form method="post" action="<?= e(url('/transactions/delete')) ?>" class="entry-delete-form">
                                <?= csrf_field() ?><input type="hidden" name="id" value="<?= e($row['id']) ?>"><button class="entry-action delete">Delete</button>
                            </form>
                        </div>
                    </div>
                </details>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="panel no-print">
        <form class="actions" method="post" action="<?= e(url('/vyaparis/roundoff')) ?>">
            <?= csrf_field() ?><input type="hidden" name="vyapari_id" value="<?= e($vyapariId) ?>"><input type="hidden" name="end_date" value="<?= e($end) ?>">
            <input type="hidden" name="grandtotal_cash" value="<?= e($closing['cash']) ?>"><input type="hidden" name="grandtotal_rtgs" value="<?= e($closing['rtgs']) ?>"><input type="hidden" name="grandtotal_cat_995" value="<?= e($closing['cat_995']) ?>">
            <button class="btn">Roundoff Carry Forward</button>
        </form>
        <form class="actions" method="post" action="<?= e(url('/vyaparis/settle')) ?>" style="margin-top:8px">
            <?= csrf_field() ?><input type="hidden" name="vyapari_id" value="<?= e($vyapariId) ?>"><button class="btn danger">Settle All Transactions</button>
        </form>
    </section>
</main>
