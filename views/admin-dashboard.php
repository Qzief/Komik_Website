<section class="panel">
    <div class="between">
        <div>
            <span class="badge">Panel admin</span>
            <h1>Kelola website komik</h1>
            <p class="muted">Admin bisa menambah komik manual memakai CDN hasil scraping atau membiarkan scraper mengisi tabel yang sama.</p>
        </div>
        <a class="button" href="<?= e(route_url('admin.manga.create')) ?>">Tambah Komik</a>
    </div>
</section>

<section class="stat-grid" style="margin-top: 24px;">
    <article class="stat-card"><span>Total Komik</span><strong><?= format_number($summary['total_manga']) ?></strong></article>
    <article class="stat-card"><span>Total Chapter</span><strong><?= format_number($summary['total_chapter']) ?></strong></article>
    <article class="stat-card"><span>Total User</span><strong><?= format_number($summary['total_user']) ?></strong></article>
    <article class="stat-card"><span>Total Vote</span><strong><?= format_number($summary['total_vote']) ?></strong></article>
</section>

<section class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Slug</th>
                    <th>Source</th>
                    <th>Chapter</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mangas as $manga): ?>
                    <tr>
                        <td><?= e($manga['judul']) ?></td>
                        <td><?= e($manga['slug']) ?></td>
                        <td><?= e($manga['source_base']) ?></td>
                        <td><?= format_number((int) $manga['real_chapter_count']) ?></td>
                        <td>
                            <div class="action-row">
                                <a class="link-button button--small" href="<?= e(route_url('comic.show', ['slug' => $manga['slug']])) ?>">Lihat</a>
                                <a class="link-button button--small" href="<?= e(route_url('admin.manga.edit', ['id' => $manga['id']])) ?>">Edit</a>
                                <a class="button button--small" href="<?= e(route_url('admin.chapter.create', ['manga_id' => $manga['id']])) ?>">Tambah Chapter</a>
                                <form method="post" action="<?= e(route_url('admin.manga.delete', ['id' => $manga['id']])) ?>" class="inline-form">
                                    <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                                    <button class="link-button button--small" type="submit" onclick="return confirm('Hapus komik ini beserta chapter?')">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
