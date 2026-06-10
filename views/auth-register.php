<section class="auth-card">
    <span class="badge">Register pengguna</span>
    <h1>Buat akun baru</h1>
    <p class="muted">Akun baru langsung bisa dipakai untuk bookmark dan voting.</p>
    <?php require __DIR__ . '/partials-errors.php'; ?>
    <form method="post" class="form-grid">
        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
        <div>
            <label>Nama</label>
            <input class="input" type="text" name="name" value="<?= e(old('name')) ?>" required>
        </div>
        <div>
            <label>Email</label>
            <input class="input" type="email" name="email" value="<?= e(old('email')) ?>" required>
        </div>
        <div>
            <label>Password</label>
            <input class="input" type="password" name="password" required>
        </div>
        <div>
            <label>Konfirmasi Password</label>
            <input class="input" type="password" name="password_confirmation" required>
        </div>
        <button class="button" type="submit">Daftar</button>
    </form>
</section>
