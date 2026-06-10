<?php
$isEdit = $mode === 'edit';
$values = [
    'slug' => old('slug', $manga['slug'] ?? ''),
    'judul' => old('judul', $manga['judul'] ?? ''),
    'manga_url' => old('manga_url', $manga['manga_url'] ?? ''),
    'external_manga_id' => old('external_manga_id', $manga['external_manga_id'] ?? ''),
    'source_base' => old('source_base', $manga['source_base'] ?? 'https://05.ikiru.wtf'),
    'thumbnail_url' => old('thumbnail_url', $manga['thumbnail_url'] ?? ''),
    'description' => old('description', $manga['description'] ?? ''),
    'genres' => old('genres', isset($manga['genres']) ? implode(', ', $manga['genres']) : ''),
    'author' => old('author', $manga['author'] ?? ''),
    'artist' => old('artist', $manga['artist'] ?? ''),
    'status' => old('status', $manga['status'] ?? 'ongoing'),
];
?>
<section class="panel">
    <span class="badge"><?= $isEdit ? 'Edit komik' : 'Tambah komik' ?></span>
    <h1><?= $isEdit ? 'Perbarui metadata komik' : 'Masukkan komik baru' ?></h1>
    <p class="muted">Gunakan `slug`, `source_base`, dan `external_manga_id` yang konsisten supaya aman ketika project scraper melakukan sinkron ulang.</p>
    <?php require __DIR__ . '/partials-errors.php'; ?>
    <form method="post" class="form-grid">
        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
        <div class="form-grid form-grid--two">
            <div>
                <label>Judul</label>
                <input class="input" type="text" name="judul" value="<?= e($values['judul']) ?>" required>
            </div>
            <div>
                <label>Slug</label>
                <input class="input" type="text" name="slug" value="<?= e($values['slug']) ?>" required>
            </div>
            <div>
                <label>Manga URL</label>
                <input class="input" type="text" name="manga_url" value="<?= e($values['manga_url']) ?>">
            </div>
            <div>
                <label>External Manga ID</label>
                <input class="input" type="text" name="external_manga_id" value="<?= e((string) $values['external_manga_id']) ?>">
            </div>
            <div>
                <label>Source Base</label>
                <input class="input" type="text" name="source_base" value="<?= e($values['source_base']) ?>" required>
            </div>
            <div>
                <label>Thumbnail URL</label>
                <input class="input" type="text" name="thumbnail_url" value="<?= e((string) $values['thumbnail_url']) ?>">
            </div>
            <div>
                <label>Author</label>
                <input class="input" type="text" name="author" value="<?= e((string) $values['author']) ?>">
            </div>
            <div>
                <label>Artist</label>
                <input class="input" type="text" name="artist" value="<?= e((string) $values['artist']) ?>">
            </div>
            <div>
                <label>Status</label>
                <select class="select" name="status">
                    <option value="ongoing" <?= $values['status'] === 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
                    <option value="completed" <?= $values['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="hiatus" <?= $values['status'] === 'hiatus' ? 'selected' : '' ?>>Hiatus</option>
                </select>
            </div>
        </div>
        <div>
            <label>Deskripsi</label>
            <textarea class="textarea" name="description"><?= e((string) $values['description']) ?></textarea>
        </div>
        <div>
            <label>Genre</label>
            <textarea class="textarea" name="genres" placeholder="Action, Fantasy, Adventure"><?= e((string) $values['genres']) ?></textarea>
        </div>
        <div class="action-row">
            <button class="button" type="submit"><?= $isEdit ? 'Simpan Perubahan' : 'Buat Komik' ?></button>
            <a class="link-button" href="<?= e(route_url('admin.dashboard')) ?>">Kembali</a>
        </div>
    </form>
</section>
