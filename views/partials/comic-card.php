<?php
$comicCardUrl = route_url('comic.show', ['slug' => $manga['slug']]);
$comicCardStatus = trim((string) ($manga['status'] ?? 'ongoing'));
$comicCardStatusLabel = $comicCardStatus !== '' ? ucfirst($comicCardStatus) : 'Ongoing';
$comicCardChapterLabel = format_number((int) ($manga['chapter_count'] ?? 0)) . ' chapter';
$comicCardNote = $comicCardNote ?? null;
?>
<article class="comic-book-card">
    <a class="comic-book-cover" href="<?= e($comicCardUrl) ?>">
        <img src="<?= e(proxy_image_url($manga['cover_url'] ?? '')) ?>" alt="<?= e($manga['judul']) ?>" loading="lazy">
    </a>
    <div class="comic-book-info">
        <h3 class="comic-book-title">
            <a href="<?= e($comicCardUrl) ?>"><?= e($manga['judul']) ?></a>
        </h3>
        <div class="comic-book-meta">
            <span class="comic-book-status"><?= e($comicCardStatusLabel) ?></span>
            <span class="comic-book-chapter"><?= e($comicCardChapterLabel) ?></span>
        </div>
        <?php if (is_string($comicCardNote) && trim($comicCardNote) !== ''): ?>
            <span class="comic-book-note"><?= e($comicCardNote) ?></span>
        <?php endif; ?>
    </div>
</article>
