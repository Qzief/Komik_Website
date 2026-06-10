<?php
$isEdit = $mode === 'edit';
$values = [
    'chapter_number' => old('chapter_number', $chapter['chapter_number'] ?? ''),
    'chapter_label' => old('chapter_label', $chapter['chapter_label'] ?? ''),
    'chapter_url' => old('chapter_url', $chapter['chapter_url'] ?? ''),
    'images' => old('images', isset($chapter['images']) ? implode(PHP_EOL, $chapter['images']) : ''),
];
?>
<section class="panel">
    <span class="badge"><?= $isEdit ? 'Edit chapter' : 'Tambah chapter' ?></span>
    <h1><?= e($manga['judul']) ?></h1>
    <p class="muted">Masukkan URL CDN satu per baris. Format ini kompatibel dengan hasil scraping yang menyimpan gambar ke tabel `chapter_images`.</p>
    <?php require __DIR__ . '/partials-errors.php'; ?>
    <form method="post" class="form-grid">
        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
        <div class="form-grid form-grid--two">
            <div>
                <label>Nomor Chapter</label>
                <input class="input" type="number" min="1" name="chapter_number" value="<?= e((string) $values['chapter_number']) ?>" required>
            </div>
            <div>
                <label>Label Chapter</label>
                <input class="input" type="text" name="chapter_label" value="<?= e($values['chapter_label']) ?>" required>
            </div>
        </div>
        <div>
            <label>URL Chapter</label>
            <input class="input" type="text" name="chapter_url" value="<?= e($values['chapter_url']) ?>" required>
        </div>
        <div>
            <label>Daftar URL Gambar CDN</label>
            <textarea class="textarea" name="images" required><?= e($values['images']) ?></textarea>
        </div>
        <div class="action-row">
            <button class="button" type="submit"><?= $isEdit ? 'Simpan Chapter' : 'Tambah Chapter' ?></button>
            <a class="link-button" href="<?= e(route_url('comic.show', ['slug' => $manga['slug']])) ?>">Kembali ke Komik</a>
        </div>
    </form>
</section>
