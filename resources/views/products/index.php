<?php use function App\Core\csrf_field; use function App\Core\e; use function App\Core\url; ?>
<div class="topbar"><h1>Products</h1><a class="btn primary" href="<?= e(url('/products/create')) ?>">New Product</a></div>
<section class="table-wrap">
    <table>
        <thead><tr><th>Product</th><th>Vyapari</th><th class="num">Purity</th><th class="num">Rate</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($products as $row): ?>
            <tr>
                <td><?= e($row['product_name']) ?></td><td><?= e($row['vyapari_name'] ?? 'Common') ?></td><td class="num"><?= e($row['purity']) ?></td><td class="num"><?= e($row['rate']) ?></td>
                <td class="actions">
                    <a class="btn" href="<?= e(url('/products/edit?id=' . $row['id'])) ?>">Edit</a>
                    <form method="post" action="<?= e(url('/products/delete')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($row['id']) ?>"><button class="btn danger">Delete</button></form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
