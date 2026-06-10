<div class="reader-chapter-picker" data-reader-picker hidden>
    <button type="button" class="reader-chapter-picker__backdrop" data-reader-picker-close aria-label="Tutup daftar chapter"></button>
    <section class="reader-chapter-picker__sheet" role="dialog" aria-modal="true" aria-labelledby="reader-picker-title">
        <div class="reader-chapter-picker__grab" aria-hidden="true"></div>
        <div class="reader-chapter-picker__header">
            <div>
                <span class="tiny-label">Navigasi chapter</span>
                <h2 id="reader-picker-title"><?= e($chapter['judul']) ?></h2>
            </div>
            <button type="button" class="reader-chapter-picker__close" data-reader-picker-close aria-label="Tutup panel chapter">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6.47 5.2a.9.9 0 0 1 1.27 0L12 9.46l4.26-4.26a.9.9 0 1 1 1.27 1.27L13.27 10.73l4.26 4.27a.9.9 0 0 1-1.27 1.27L12 12l-4.26 4.27A.9.9 0 1 1 6.47 15l4.26-4.27L6.47 6.47a.9.9 0 0 1 0-1.27Z"/>
                </svg>
            </button>
        </div>

        <div class="reader-chapter-picker__toolbar">
            <span class="reader-chapter-picker__toolbar-label">Urutan</span>
            <div class="reader-order-toggle" data-reader-order-toggle>
                <button type="button" data-reader-order-button="desc">Terbaru</button>
                <button type="button" data-reader-order-button="asc">Awal</button>
            </div>
        </div>

        <div class="reader-chapter-picker__list" data-reader-chapter-list>
            <?php foreach ($chapter['chapter_list'] as $chapterItem): ?>
                <?php $isCurrentChapter = (int) $chapterItem['chapter_number'] === (int) $chapter['chapter_number']; ?>
                <a
                    class="reader-chapter-picker__item<?= $isCurrentChapter ? ' is-current' : '' ?>"
                    href="<?= e(route_url('reader', ['slug' => $chapter['slug'], 'chapter' => $chapterItem['chapter_number']])) ?>"
                    data-reader-chapter-item
                    data-chapter-number="<?= e((string) $chapterItem['chapter_number']) ?>"
                    <?= $isCurrentChapter ? 'aria-current="page"' : '' ?>
                >
                    <span class="reader-chapter-picker__item-copy">
                        <strong><?= e($chapterItem['chapter_label']) ?></strong>
                        <span><?= $isCurrentChapter ? 'Sedang dibuka' : 'Buka chapter ini' ?></span>
                    </span>
                    <span class="reader-chapter-picker__item-number">#<?= e((string) $chapterItem['chapter_number']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</div>
