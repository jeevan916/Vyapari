<?php use function App\Core\csrf_field; use function App\Core\e; use function App\Core\url; ?>
<?php $isEdit = !empty($vyapari['id']); ?>
<div class="topbar"><h1><?= e($title) ?></h1></div>
<form class="panel grid cols-2" method="post" action="<?= e(url($isEdit ? '/vyaparis/update' : '/vyaparis/store')) ?>">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= e($vyapari['id']) ?>"><?php endif; ?>
    <label>Vyapari Name <input name="vyapari_name" required value="<?= e($vyapari['vyapari_name'] ?? '') ?>"></label>
    <label>Company Name <input name="company_name" value="<?= e($vyapari['company_name'] ?? '') ?>"></label>
    <label>Email <input type="email" name="email_id" value="<?= e($vyapari['email_id'] ?? '') ?>"></label>
    <label>Primary Number <input name="primary_number" value="<?= e($vyapari['primary_number'] ?? '') ?>"></label>
    <label>Secondary Number <input name="secondary_number" value="<?= e($vyapari['secondary_number'] ?? '') ?>"></label>
    <label>GST Number <input name="gst_number" value="<?= e($vyapari['gst_number'] ?? '') ?>"></label>
    <label>City <input name="city" value="<?= e($vyapari['city'] ?? '') ?>"></label>
    <label>State <input name="state" value="<?= e($vyapari['state'] ?? '') ?>"></label>
    <div class="form-actions"><button class="btn primary">Save</button><a class="btn" href="<?= e(url('/vyaparis')) ?>">Cancel</a></div>
</form>
