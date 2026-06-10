<?php
$readerDetailUrl = route_url('comic.show', ['slug' => $chapter['slug']]);
$readerPrevUrl = $chapter['prev_chapter']
    ? route_url('reader', ['slug' => $chapter['slug'], 'chapter' => $chapter['prev_chapter']])
    : null;
$readerNextUrl = $chapter['next_chapter']
    ? route_url('reader', ['slug' => $chapter['slug'], 'chapter' => $chapter['next_chapter']])
    : null;
?>
<div class="reader-header-stack">
    <div class="reader-mobile-topbar reader-mobile-chrome">
        <a class="reader-mobile-topbar__back" href="<?= e($readerDetailUrl) ?>" aria-label="Kembali ke detail">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M14.9 5.1a.9.9 0 0 1 0 1.27L10.27 11l4.63 4.63a.9.9 0 0 1-1.27 1.27l-5.27-5.27a.9.9 0 0 1 0-1.27l5.27-5.27a.9.9 0 0 1 1.27 0Z"/>
            </svg>
        </a>
        <div class="reader-mobile-topbar__copy">
            <strong><?= e($chapter['judul']) ?></strong>
            <span><?= e($chapter['chapter_label']) ?></span>
        </div>
    </div>

    <div class="reader-head reader-head--desktop">
        <div class="reader-head__copy">
            <span class="badge"><?= e($chapter['chapter_label']) ?></span>
            <h1><?= e($chapter['judul']) ?></h1>
        </div>
        <div class="action-row reader-head__actions">
            <a class="link-button" href="<?= e($readerDetailUrl) ?>">Kembali ke Detail</a>
            <?php if ($readerPrevUrl): ?>
                <a class="link-button" href="<?= e($readerPrevUrl) ?>">Chapter Sebelumnya</a>
            <?php endif; ?>
            <?php if ($readerNextUrl): ?>
                <a class="button" href="<?= e($readerNextUrl) ?>">Chapter Berikutnya</a>
            <?php endif; ?>
        </div>
    </div>
</div>
