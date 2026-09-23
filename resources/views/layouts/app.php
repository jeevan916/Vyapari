<?php

use App\Core\Auth;
use function App\Core\csrf_field;
use function App\Core\e;
use function App\Core\flash;
use function App\Core\url;

$appName = config('app.name');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($title ?? 'App') . ' - ' . $appName) ?></title>
    <link rel="stylesheet" href="<?= e(url('assets/app.css')) ?>">
</head>
<body>
<?php if (Auth::check()): ?>
    <header class="navbar">
        <a class="brand" href="<?= e(url('/')) ?>"><?= e($appName) ?></a>
        <nav>
            <a href="<?= e(url('/')) ?>">Home</a>
            <a href="<?= e(url('/vyaparis')) ?>">Vyapari</a>
            <a href="<?= e(url('/products')) ?>">Products</a>
            <a href="<?= e(url('/transactions')) ?>">Transactions</a>
        </nav>
        <form method="post" action="<?= e(url('/logout')) ?>">
            <?= csrf_field() ?>
            <button class="logout">Logout (<?= e(Auth::user()['name'] ?? 'Admin') ?>)</button>
        </form>
    </header>
<?php endif; ?>
<main class="<?= Auth::check() ? 'main container-fluid' : 'auth-main' ?>">
    <?php if ($message = flash('success')): ?><div class="alert success"><?= e($message) ?></div><?php endif; ?>
    <?php if ($message = flash('error')): ?><div class="alert error"><?= e($message) ?></div><?php endif; ?>
    <?php require $viewPath; ?>
</main>
<script src="<?= e(url('assets/app.js')) ?>"></script>
</body>
</html>
