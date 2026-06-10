<section class="reader-shell reader-shell--enhanced" data-reader-shell>
    <?php require __DIR__ . '/layouts/reader-mobile-header.php'; ?>
    <div class="reader-images">
        <?php foreach ($chapter['images'] as $image): ?>
            <img src="<?= e($image['image_url']) ?>" alt="<?= e($chapter['judul'] . ' - gambar ' . $image['image_order']) ?>" loading="lazy">
        <?php endforeach; ?>
    </div>
    <?php require __DIR__ . '/layouts/reader-mobile-controls.php'; ?>
    <?php require __DIR__ . '/layouts/reader-chapter-picker.php'; ?>
</section>
