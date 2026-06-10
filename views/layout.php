<?php /** @var string $content */ ?>
<?php $routeName = current_route(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? config('app.name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset_url('assets/css/base.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('assets/css/layout.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('assets/css/comic-card.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('assets/css/mobile-navbar.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('assets/css/responsive.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('assets/css/search-ai.css')) ?>">
    <?php if ($routeName === 'reader'): ?>
        <link rel="stylesheet" href="<?= e(asset_url('assets/css/reader.css')) ?>">
        <link rel="stylesheet" href="<?= e(asset_url('assets/css/reader-mobile.css')) ?>">
    <?php endif; ?>
    <?php if ($routeName === 'home'): ?>
        <link rel="stylesheet" href="<?= e(asset_url('assets/css/home.css')) ?>">
    <?php endif; ?>
</head>
<body class="route-<?= e(str_replace('.', '-', $routeName)) ?>">
    <div class="site-shell">
    <header class="topbar">
        <div class="topbar__wrap">
            <a href="<?= e(route_url('home')) ?>" class="brand">
                <span class="brand__mark">KH</span>
                <span class="brand__text">
                    <strong><?= e(config('app.name')) ?></strong>
                    <small>Reader library</small>
                </span>
            </a>
            <?php if ($routeName !== 'reader'): ?>
                <form
                    method="get"
                    action="<?= e(route_url('home')) ?>"
                    class="topbar-search topbar-search--ai"
                    aria-label="Cari komik"
                    data-search-ai
                    data-search-endpoint="<?= e(route_url('search.ai')) ?>"
                >
                    <input type="hidden" name="route" value="home">
                    <span class="topbar-search__ai" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 2.8l1.83 5.37L19.2 10l-5.37 1.83L12 17.2l-1.83-5.37L4.8 10l5.37-1.83L12 2.8Z"/>
                        </svg>
                        <span>AI</span>
                    </span>
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M10.8 4a6.8 6.8 0 0 1 5.38 10.96l3.43 3.43a.9.9 0 0 1-1.27 1.27l-3.43-3.43A6.8 6.8 0 1 1 10.8 4Zm0 1.8a5 5 0 1 0 0 10 5 5 0 0 0 0-10Z"/>
                    </svg>
                    <input type="text" name="search" value="<?= e($_GET['search'] ?? '') ?>" placeholder='Coba: "romance kerajaan cewek kuat"' autocomplete="off" data-search-ai-input>
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
            <?php endif; ?>
            <div class="nav">
                <nav class="nav__links" aria-label="Navigasi utama">
                    <a class="<?= $routeName === 'home' ? 'is-active' : '' ?>" href="<?= e(route_url('home')) ?>">Beranda</a>
                    <?php if (is_logged_in()): ?>
                        <a class="<?= $routeName === 'bookmarks' ? 'is-active' : '' ?>" href="<?= e(route_url('bookmarks')) ?>">Bookmark</a>
                        <?php if (is_admin()): ?>
                            <a class="<?= str_starts_with($routeName, 'admin.') ? 'is-active' : '' ?>" href="<?= e(route_url('admin.dashboard')) ?>">Admin</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a class="<?= $routeName === 'login' ? 'is-active' : '' ?>" href="<?= e(route_url('login')) ?>">Login</a>
                    <?php endif; ?>
                </nav>
                <div class="nav__actions">
                    <?php if (is_logged_in()): ?>
                        <span class="user-pill"><?= e(current_user()['name'] ?? current_user()['email'] ?? 'User') ?></span>
                        <form method="post" action="<?= e(route_url('logout')) ?>" class="inline-form">
                            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                            <button type="submit" class="link-button button--small">Logout</button>
                        </form>
                    <?php else: ?>
                        <a href="<?= e(route_url('register')) ?>" class="button button--small">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    <main class="page <?= $routeName === 'reader' ? 'page--reader' : '' ?>">
        <div class="container <?= $routeName === 'reader' ? 'container--reader' : '' ?>">
            <?php if ($message = flash_get('success')): ?>
                <div class="alert alert--success"><?= e($message) ?></div>
            <?php endif; ?>
            <?php if ($message = flash_get('error')): ?>
                <div class="alert alert--error"><?= e($message) ?></div>
            <?php endif; ?>
            <?= $content ?>
        </div>
    </main>
    </div>
    <?php require dirname(__DIR__) . '/public/layouts/mobile-navbar.php'; ?>
    <script src="<?= e(asset_url('assets/js/search-ai.js')) ?>" defer></script>
    <?php if ($routeName === 'reader'): ?>
        <script src="<?= e(asset_url('assets/js/reader-mobile.js')) ?>" defer></script>
    <?php endif; ?>
</body>
</html>
