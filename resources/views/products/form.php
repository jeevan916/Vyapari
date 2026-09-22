<?php use function App\Core\csrf_field; use function App\Core\e; use function App\Core\url; ?>
<?php $isEdit = !empty($product['id']); ?>
<div class="topbar"><h1><?= e($title) ?></h1></div>
<form class="panel grid cols-2" method="post" action="<?= e(url($isEdit ? '/products/update' : '/products/store')) ?>">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= e($product['id']) ?>"><?php endif; ?>
    <label>Vyapari
        <select name="vyapari_id">
            <option value="">Common Product</option>
            <?php foreach ($vyaparis as $v): ?><option value="<?= e($v['id']) ?>" <?= (($product['vyapari_id'] ?? '') == $v['id']) ? 'selected' : '' ?>><?= e($v['vyapari_name']) ?></option><?php endforeach; ?>
        </select>
    </label>
    <label>Product Name <input name="product_name" required value="<?= e($product['product_name'] ?? '') ?>"></label>
    <label>Purity <input type="number" step="0.001" name="purity" value="<?= e($product['purity'] ?? '0') ?>"></label>
    <label>Rate <input type="number" step="0.001" name="rate" value="<?= e($product['rate'] ?? '0') ?>"></label>
    <div class="form-actions"><button class="btn primary">Save</button><a class="btn" href="<?= e(url('/products')) ?>">Cancel</a></div>
</form>
