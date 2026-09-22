<?php use function App\Core\csrf_field; use function App\Core\e; use function App\Core\url; ?>
<div class="topbar">
    <h1>Vyaparis</h1>
    <a class="btn primary" href="<?= e(url('/vyaparis/create')) ?>">New Vyapari</a>
</div>
<form class="panel actions" method="get" action="<?= e(url('/vyaparis')) ?>">
    <input name="q" placeholder="Search name, company, phone" value="<?= e($_GET['q'] ?? '') ?>">
    <button class="btn">Search</button>
</form>
<section class="table-wrap" style="margin-top:18px">
    <table>
        <thead><tr><th>Name</th><th>Company</th><th>Phone</th><th>GST</th><th>City</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($vyaparis as $row): ?>
            <tr>
                <td><?= e($row['vyapari_name']) ?></td><td><?= e($row['company_name']) ?></td><td><?= e($row['primary_number']) ?></td><td><?= e($row['gst_number']) ?></td><td><?= e($row['city']) ?></td>
                <td class="actions">
                    <a class="btn" href="<?= e(url('/vyaparis/edit?id=' . $row['id'])) ?>">Edit</a>
                    <a class="btn" href="<?= e(url('/ledger?vyapari_id=' . $row['id'])) ?>">Ledger</a>
                    <form method="post" action="<?= e(url('/vyaparis/delete')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($row['id']) ?>"><button class="btn danger">Delete</button></form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
