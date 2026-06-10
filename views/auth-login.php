<section class="auth-card">
    <span class="badge">Login pengguna</span>
    <h1>Masuk ke akun</h1>
    <p class="muted">Login untuk like, bookmark, dan voting kelanjutan komik.</p>
    <?php require __DIR__ . '/partials-errors.php'; ?>
    <form method="post" class="form-grid">
        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
        <div>
            <label>Email</label>
            <input class="input" type="email" name="email" value="<?= e(old('email')) ?>" required>
        </div>
        <div>
            <label>Password</label>
            <input class="input" type="password" name="password" required>
        </div>
        <button class="button" type="submit">Login</button>
    </form>
</section>
