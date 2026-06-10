<section class="panel">
    <span class="badge">Daftar tersimpan</span>
    <h1>Bookmark saya</h1>
    <p class="muted">Semua komik yang disimpan pengguna aktif tampil di sini.</p>
</section>

<section class="grid comic-book-grid">
    <?php foreach ($items as $manga): ?>
        <?php $comicCardNote = 'Disimpan pada ' . format_datetime($manga['bookmarked_at']); ?>
        <?php require __DIR__ . '/partials/comic-card.php'; ?>
        <?php $comicCardNote = null; ?>
    <?php endforeach; ?>

    <?php if ($items === []): ?>
        <div class="panel">
            <p class="muted">Belum ada bookmark. Simpan komik dari halaman detail untuk melihatnya di sini.</p>
        </div>
    <?php endif; ?>
</section>
