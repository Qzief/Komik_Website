<?php
$readerPrevUrl = $chapter['prev_chapter']
    ? route_url('reader', ['slug' => $chapter['slug'], 'chapter' => $chapter['prev_chapter']])
    : null;
$readerNextUrl = $chapter['next_chapter']
    ? route_url('reader', ['slug' => $chapter['slug'], 'chapter' => $chapter['next_chapter']])
    : null;
?>
<div class="reader-mobile-controls reader-mobile-chrome" data-reader-mobile-controls>
    <div class="reader-mobile-controls__bar" data-reader-controls-bar>
        <?php if ($readerPrevUrl): ?>
            <a class="reader-mobile-controls__nav" href="<?= e($readerPrevUrl) ?>" aria-label="Chapter sebelumnya" title="Chapter sebelumnya">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M14.9 5.1a.9.9 0 0 1 0 1.27L10.27 11l4.63 4.63a.9.9 0 0 1-1.27 1.27l-5.27-5.27a.9.9 0 0 1 0-1.27l5.27-5.27a.9.9 0 0 1 1.27 0Z"/>
                </svg>
            </a>
        <?php else: ?>
            <span class="reader-mobile-controls__nav is-disabled" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <path d="M14.9 5.1a.9.9 0 0 1 0 1.27L10.27 11l4.63 4.63a.9.9 0 0 1-1.27 1.27l-5.27-5.27a.9.9 0 0 1 0-1.27l5.27-5.27a.9.9 0 0 1 1.27 0Z"/>
                </svg>
            </span>
        <?php endif; ?>

        <button type="button" class="reader-mobile-controls__picker" data-reader-picker-open aria-label="Buka daftar chapter" title="Daftar chapter">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M6 4.8h12a1.2 1.2 0 0 1 1.2 1.2v12A1.2 1.2 0 0 1 18 19.2H6A1.2 1.2 0 0 1 4.8 18V6A1.2 1.2 0 0 1 6 4.8Zm1.2 3v1.6h9.6V7.8H7.2Zm0 3.4v1.6h6.2v-1.6H7.2Zm0 3.4v1.6h8.1v-1.6H7.2Z"/>
            </svg>
        </button>

        <?php if ($readerNextUrl): ?>
            <a class="reader-mobile-controls__nav reader-mobile-controls__nav--primary" href="<?= e($readerNextUrl) ?>" aria-label="Chapter berikutnya" title="Chapter berikutnya">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M9.1 18.9a.9.9 0 0 1 0-1.27L13.73 13 9.1 8.37a.9.9 0 0 1 1.27-1.27l5.27 5.27a.9.9 0 0 1 0 1.27l-5.27 5.26a.9.9 0 0 1-1.27 0Z"/>
                </svg>
            </a>
        <?php else: ?>
            <span class="reader-mobile-controls__nav reader-mobile-controls__nav--primary is-disabled" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <path d="M9.1 18.9a.9.9 0 0 1 0-1.27L13.73 13 9.1 8.37a.9.9 0 0 1 1.27-1.27l5.27 5.27a.9.9 0 0 1 0 1.27l-5.27 5.26a.9.9 0 0 1-1.27 0Z"/>
                </svg>
            </span>
        <?php endif; ?>

        <button type="button" class="reader-mobile-controls__toggle" data-reader-controls-collapse aria-label="Sembunyikan menu baca" title="Sembunyikan menu baca">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M5.8 7.2h12.4a.9.9 0 1 1 0 1.8H5.8a.9.9 0 1 1 0-1.8Zm3.2 4.4h9.2a.9.9 0 1 1 0 1.8H9a.9.9 0 1 1 0-1.8Zm-3.2 4.4h12.4a.9.9 0 1 1 0 1.8H5.8a.9.9 0 1 1 0-1.8Z"/>
            </svg>
        </button>
    </div>
</div>
