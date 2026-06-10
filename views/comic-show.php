<section class="split comic-layout">
    <aside class="stack comic-sidebar">
        <div class="cover panel comic-cover">
            <img src="<?= e(proxy_image_url($manga['cover_url'] ?? '')) ?>" alt="<?= e($manga['judul']) ?>" loading="lazy">
        </div>
        <div class="panel comic-summary">
            <div class="meta-row comic-summary__badges">
                <span class="badge"><?= e($manga['status'] ?? 'ongoing') ?></span>
                <span><?= format_number((int) $manga['chapter_count']) ?> chapter</span>
            </div>
            <div class="comic-summary__stats">
                <div class="comic-summary__stat">
                    <strong><?= format_number((int) $manga['like_count']) ?></strong>
                    <span>Like</span>
                </div>
                <div class="comic-summary__stat">
                    <strong><?= format_number((int) $manga['bookmark_count']) ?></strong>
                    <span>Bookmark</span>
                </div>
                <div class="comic-summary__stat">
                    <strong><?= format_number((int) $manga['continue_count']) ?></strong>
                    <span>Vote lanjut</span>
                </div>
            </div>
            <?php if (!empty($manga['author']) || !empty($manga['artist'])): ?>
                <div class="meta-row comic-summary__credits">
                    <?php if (!empty($manga['author'])): ?><span>Author: <?= e($manga['author']) ?></span><?php endif; ?>
                    <?php if (!empty($manga['artist'])): ?><span>Artist: <?= e($manga['artist']) ?></span><?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if (is_logged_in()): ?>
                <div class="action-row comic-summary__actions">
                    <form method="post" action="<?= e(route_url('comic.like')) ?>" class="inline-form">
                        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="manga_id" value="<?= e((string) $manga['id']) ?>">
                        <button class="button" type="submit"><?= (int) $manga['is_liked'] === 1 ? 'Batal Like' : 'Like Komik' ?></button>
                    </form>
                    <form method="post" action="<?= e(route_url('comic.bookmark')) ?>" class="inline-form">
                        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="manga_id" value="<?= e((string) $manga['id']) ?>">
                        <button class="link-button" type="submit"><?= (int) $manga['is_bookmarked'] === 1 ? 'Hapus Bookmark' : 'Simpan Bookmark' ?></button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </aside>

    <section class="stack comic-main">
        <div class="panel comic-hero">
            <div class="comic-hero__eyebrow">
                <span class="badge">Kompatibel dengan `ComicsScraping`</span>
                <span class="tiny-label">Detail Series</span>
            </div>
            <h1 class="section-title comic-hero__title"><?= e($manga['judul']) ?></h1>
            <p class="muted comic-hero__desc"><?= nl2br(e($manga['description'] ?? 'Belum ada deskripsi untuk komik ini.')) ?></p>
            <?php if (!empty($manga['genres'])): ?>
                <div class="meta-row comic-hero__meta">
                    <?php foreach ($manga['genres'] as $genre): ?>
                        <span class="badge"><?= e($genre) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="meta-row comic-hero__meta">
                <span>Source: <?= e($manga['source_base']) ?></span>
                <span>External ID: <?= e($manga['external_manga_id'] ?: '-') ?></span>
                <span>Update: <?= e(format_datetime($manga['updated_at'])) ?></span>
            </div>
        </div>

        <div class="panel vote-panel">
            <div class="between">
                <div>
                    <h2>Voting kelanjutan</h2>
                    <p class="muted">User bisa memilih apakah komik ini layak dilanjutkan update atau tidak.</p>
                </div>
                <div class="meta-row">
                    <span>Lanjut: <?= format_number((int) $manga['continue_count']) ?></span>
                    <span>Stop: <?= format_number((int) $manga['stop_count']) ?></span>
                </div>
            </div>
            <?php if (is_logged_in()): ?>
                <div class="action-row">
                    <form method="post" action="<?= e(route_url('comic.vote')) ?>">
                        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="manga_id" value="<?= e((string) $manga['id']) ?>">
                        <input type="hidden" name="vote_type" value="continue">
                        <button class="button" type="submit"><?= ($manga['current_vote'] ?? '') === 'continue' ? 'Vote Lanjut Tersimpan' : 'Vote Lanjutkan' ?></button>
                    </form>
                    <form method="post" action="<?= e(route_url('comic.vote')) ?>">
                        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="manga_id" value="<?= e((string) $manga['id']) ?>">
                        <input type="hidden" name="vote_type" value="stop">
                        <button class="link-button" type="submit"><?= ($manga['current_vote'] ?? '') === 'stop' ? 'Vote Stop Tersimpan' : 'Vote Stop' ?></button>
                    </form>
                </div>
            <?php else: ?>
                <p class="muted">Login untuk ikut voting, like, dan bookmark.</p>
            <?php endif; ?>
        </div>

        <div class="panel chapter-panel">
            <div class="between">
                <h2>Daftar Chapter</h2>
                <?php if (is_admin()): ?>
                    <a class="button button--small" href="<?= e(route_url('admin.chapter.create', ['manga_id' => $manga['id']])) ?>">Tambah Chapter</a>
                <?php endif; ?>
            </div>
            <div class="chapter-list">
                <?php foreach ($manga['chapters'] as $chapter): ?>
                    <article class="chapter-card">
                        <div class="chapter-card__row">
                            <div>
                                <strong><?= e($chapter['chapter_label']) ?></strong>
                                <div class="meta-row">
                                    <span><?= format_number((int) $chapter['image_count']) ?> gambar</span>
                                    <span><?= e(format_datetime($chapter['updated_at'])) ?></span>
                                </div>
                            </div>
                            <div class="action-row">
                                <a class="button button--small" href="<?= e(route_url('reader', ['slug' => $manga['slug'], 'chapter' => $chapter['chapter_number']])) ?>">Baca</a>
                                <?php if (is_admin()): ?>
                                    <a class="link-button button--small" href="<?= e(route_url('admin.chapter.edit', ['id' => $chapter['id']])) ?>">Edit</a>
                                    <form method="post" action="<?= e(route_url('admin.chapter.delete', ['id' => $chapter['id']])) ?>" class="inline-form">
                                        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                                        <button class="link-button button--small" type="submit" onclick="return confirm('Hapus chapter ini?')">Hapus</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</section>
