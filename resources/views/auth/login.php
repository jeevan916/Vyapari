<?php use function App\Core\csrf_field; use function App\Core\e; use function App\Core\url; ?>
<section class="auth-card panel">
    <h1>Login</h1>
    <p class="muted">Sign in to manage vyapari ledger entries.</p>
    <form method="post" action="<?= e(url('/login')) ?>" class="grid">
        <?= csrf_field() ?>
        <label>Email <input type="email" name="email" required autofocus></label>
        <label>Password <input type="password" name="password" required></label>
        <button class="btn primary">Login</button>
    </form>
</section>
