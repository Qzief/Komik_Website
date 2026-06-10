<?php
$items = $result['items'];
$featured = $result['featured'] ?? $items[0] ?? null;
$secondaryFeatured = array_values(array_filter(
    $items,
    static fn (array $manga): bool => !$featured || $manga['slug'] !== $featured['slug']
));
$searchIntent = $searchIntent ?? build_ai_search_intent($search ?? '');
$totalChapters = array_reduce($items, static fn (int $carry, array $manga): int => $carry + (int) ($manga['chapter_count'] ?? 0), 0);
$totalLikes = array_reduce($items, static fn (int $carry, array $manga): int => $carry + (int) ($manga['like_count'] ?? 0), 0);
$totalBookmarks = array_reduce($items, static fn (int $carry, array $manga): int => $carry + (int) ($manga['bookmark_count'] ?? 0), 0);
?>

<section class="home-hero panel">
    <div class="home-hero__grid">
        <div class="home-hero__copy">
            <div class="catalog-kicker">
                <span class="eyebrow">Katalog Komik</span>
                <span class="catalog-kicker__dot" aria-hidden="true"></span>
                <span class="catalog-kicker__note">Rak baca yang lebih rapi, cepat discan, dan enak dipilih</span>
            </div>
            <h1 class="catalog-title">Temukan komik yang <span class="catalog-title__accent">layak dibuka duluan</span>.</h1>
            <p class="catalog-lead">Halaman depan ini dibuat buat scan cepat: lihat cover, cek status, baca ringkasan singkat, lalu langsung masuk ke seri yang paling menarik.</p>
            <div class="hero-stats">
                <div class="hero-stat">
                    <strong><?= format_number(count($items)) ?></strong>
                    <span>Judul tampil</span>
                </div>
                <div class="hero-stat">
                    <strong><?= format_number($totalChapters) ?></strong>
                    <span>Total chapter</span>
                </div>
                <div class="hero-stat">
                    <strong><?= format_number($totalLikes + $totalBookmarks) ?></strong>
                    <span>Interaksi user</span>
                </div>
            </div>
        </div>
        <div class="home-hero__visual">
            <?php if ($featured): ?>
                <article class="feature-stage">
                    <div class="feature-stage__ambient" aria-hidden="true"></div>
                    <div class="feature-stage__media">
                        <?php foreach (array_slice($secondaryFeatured, 0, 2) as $index => $stackItem): ?>
                            <a class="feature-stage__ghost feature-stage__ghost--<?= $index + 1 ?>" href="<?= e(route_url('comic.show', ['slug' => $stackItem['slug']])) ?>" aria-hidden="true" tabindex="-1">
                                <img src="<?= e(proxy_image_url($stackItem['cover_url'] ?? '')) ?>" alt="" loading="lazy">
                            </a>
                        <?php endforeach; ?>
                        <a class="feature-stage__cover" href="<?= e(route_url('comic.show', ['slug' => $featured['slug']])) ?>" aria-label="Buka <?= e($featured['judul']) ?>">
                            <img src="<?= e(proxy_image_url($featured['cover_url'] ?? '')) ?>" alt="<?= e($featured['judul']) ?>" loading="eager">
                        </a>
                    </div>
                    <div class="feature-stage__panel">
                        <div class="feature-stage__eyebrow">
                            <span class="tiny-label">Pilihan minggu ini</span>
                            <span class="feature-stage__status"><?= e(ucfirst((string) ($featured['status'] ?? 'ongoing'))) ?></span>
                        </div>
                        <h2><?= e($featured['judul']) ?></h2>
                        <?php if (!empty($featured['genres'])): ?>
                            <div class="feature-stage__tags">
                                <?php foreach (array_slice($featured['genres'], 0, 3) as $genre): ?>
                                    <span><?= e($genre) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <p><?= e(str_limit($featured['description'] ?? 'Komik pilihan dari katalog utama, cocok untuk mulai baca dari chapter terbaru.', 95)) ?></p>
                        <div class="feature-stage__footer">
                            <div class="feature-stage__micro">
                                <span><?= format_number((int) $featured['chapter_count']) ?> chapter</span>
                                <span><?= format_number((int) $featured['bookmark_count']) ?> simpan</span>
                            </div>
                            <a class="link-button button--small feature-stage__button" href="<?= e(route_url('comic.show', ['slug' => $featured['slug']])) ?>">Lihat Detail</a>
                        </div>
                    </div>
                </article>
            <?php elseif (!is_logged_in()): ?>
                <article class="feature-stage feature-stage--empty">
                    <div class="feature-stage__panel">
                        <div class="feature-stage__eyebrow">
                            <span class="tiny-label">Mulai koleksi</span>
                        </div>
                        <h2>Simpan komik favoritmu lebih cepat.</h2>
                        <p>Daftar untuk mulai bookmark, like, dan vote komik yang layak lanjut update.</p>
                        <div class="feature-stage__footer">
                            <a class="link-button button--small feature-stage__button" href="<?= e(route_url('register')) ?>">Daftar Sekarang</a>
                        </div>
                    </div>
                </article>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="home-mobile-search panel" id="mobile-search">
    <form
        method="get"
        action="<?= e(route_url('home')) ?>"
        class="topbar-search topbar-search--ai home-mobile-search__form"
        aria-label="Cari komik"
        data-search-ai
        data-search-endpoint="<?= e(route_url('search.ai')) ?>"
    >
        <input type="hidden" name="route" value="home">
        <input type="hidden" name="sort" value="<?= e($sort) ?>">
        <span class="topbar-search__ai" aria-hidden="true">
            <svg viewBox="0 0 24 24">
                <path d="M12 2.8l1.83 5.37L19.2 10l-5.37 1.83L12 17.2l-1.83-5.37L4.8 10l5.37-1.83L12 2.8Z"/>
            </svg>
            <span>AI</span>
        </span>
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M10.8 4a6.8 6.8 0 0 1 5.38 10.96l3.43 3.43a.9.9 0 0 1-1.27 1.27l-3.43-3.43A6.8 6.8 0 1 1 10.8 4Zm0 1.8a5 5 0 1 0 0 10 5 5 0 0 0 0-10Z"/>
        </svg>
        <input type="text" name="search" value="<?= e($search) ?>" placeholder='Coba: "romance kerajaan cewek kuat"' autocomplete="off" data-search-ai-input>
        <div class="search-ai-dropdown" data-search-ai-panel hidden>
            <div class="search-ai-dropdown__header">
                <div class="search-ai-dropdown__title">
                    <span class="search-ai-dropdown__spark" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 2.8l1.83 5.37L19.2 10l-5.37 1.83L12 17.2l-1.83-5.37L4.8 10l5.37-1.83L12 2.8Z"/>
                        </svg>
                    </span>
                    <strong>AI Search</strong>
                </div>
                <span class="search-ai-dropdown__provider" data-search-ai-provider>AI</span>
            </div>
            <p class="search-ai-dropdown__status" data-search-ai-status>Coba cari dengan bahasa bebas.</p>
            <div class="search-ai-dropdown__body">
                <div class="search-ai-dropdown__loading" data-search-ai-loading hidden>
                    <div class="search-ai-dropdown__skeleton"></div>
                    <div class="search-ai-dropdown__skeleton"></div>
                    <div class="search-ai-dropdown__skeleton"></div>
                </div>
                <div class="search-ai-dropdown__results" data-search-ai-results></div>
                <div class="search-ai-dropdown__empty" data-search-ai-empty hidden>AI belum menemukan komik yang cocok.</div>
            </div>
            <div class="search-ai-dropdown__suggestions">
                <span class="search-ai-dropdown__suggestions-label">Saran Katalog</span>
                <div class="search-ai-dropdown__chips" data-search-ai-chips></div>
            </div>
        </div>
    </form>
</section>

<section class="library-head" id="library">
    <div>
        <span class="eyebrow">Library</span>
        <h2 class="section-title">Komik yang siap dibaca sekarang</h2>
        <p class="muted">Deretan seri yang lagi ramai, baru update, dan paling enak buat langsung lanjut baca.</p>
    </div>
</section>

<?php if ($items !== []): ?>
    <section class="grid comic-book-grid">
        <?php foreach ($items as $manga): ?>
            <?php require __DIR__ . '/partials/comic-card.php'; ?>
        <?php endforeach; ?>
    </section>
<?php else: ?>
    <section class="panel search-empty">
        <span class="badge">AI Search</span>
        <h2>Tidak ada hasil yang cocok.</h2>
        <p class="muted">AI belum menemukan komik yang pas untuk "<?= e($search) ?>". Coba arah yang lebih spesifik atau pilih salah satu saran berikut.</p>
        <div class="search-ai__chips">
            <?php foreach (($searchIntent['suggestions'] ?? []) as $suggestion): ?>
                <a class="search-ai__chip" href="<?= e(route_url('home', ['search' => $suggestion, 'sort' => $sort])) ?>"><?= e($suggestion) ?></a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php if ($result['last_page'] > 1): ?>
    <section class="panel home-pagination">
        <div class="between">
            <span>Halaman <?= e((string) $result['page']) ?> dari <?= e((string) $result['last_page']) ?></span>
            <div class="action-row">
                <?php if ($result['page'] > 1): ?>
                    <a class="link-button" href="<?= e(route_url('home', ['page' => $result['page'] - 1, 'search' => $search, 'sort' => $sort])) ?>">Sebelumnya</a>
                <?php endif; ?>
                <?php if ($result['page'] < $result['last_page']): ?>
                    <a class="button" href="<?= e(route_url('home', ['page' => $result['page'] + 1, 'search' => $search, 'sort' => $sort])) ?>">Berikutnya</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

