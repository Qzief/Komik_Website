<?php
$mobileAccountRoute = is_logged_in()
    ? (is_admin() ? route_url('admin.dashboard') : route_url('bookmarks'))
    : route_url('login');
$mobileAccountLabel = is_logged_in()
    ? (is_admin() ? 'Admin' : 'Akun')
    : 'Masuk';
$mobileSearchRoute = route_url('home') . '#mobile-search';
$mobileLibraryRoute = route_url('home') . '#library';
$mobileBookmarkRoute = is_logged_in() ? route_url('bookmarks') : route_url('login');
?>
<nav class="mobile-bottom-nav" aria-label="Navigasi mobile">
    <div class="mobile-bottom-nav__inner">
        <a class="mobile-bottom-nav__item <?= $routeName === 'home' ? 'is-active' : '' ?>" href="<?= e(route_url('home')) ?>" aria-label="Beranda">
            <span class="mobile-bottom-nav__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <path d="M3 10.8 12 3l9 7.8v9.4a.8.8 0 0 1-.8.8h-5.1v-6.4H8.9V21H3.8a.8.8 0 0 1-.8-.8v-9.4Z"/>
                </svg>
            </span>
            <span>Home</span>
        </a>
        <a class="mobile-bottom-nav__item" href="<?= e($mobileSearchRoute) ?>" aria-label="Cari komik">
            <span class="mobile-bottom-nav__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <path d="M10.8 4a6.8 6.8 0 0 1 5.38 10.96l3.43 3.43a.9.9 0 0 1-1.27 1.27l-3.43-3.43A6.8 6.8 0 1 1 10.8 4Zm0 1.8a5 5 0 1 0 0 10 5 5 0 0 0 0-10Z"/>
                </svg>
            </span>
            <span>Search</span>
        </a>
        <a class="mobile-bottom-nav__item <?= $routeName === 'bookmarks' ? 'is-active' : '' ?>" href="<?= e($mobileBookmarkRoute) ?>" aria-label="Bookmark">
            <span class="mobile-bottom-nav__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <path d="M6.2 4h11.6a.9.9 0 0 1 .9.9v15.2a.7.7 0 0 1-1.08.59L12 17.06l-5.62 3.63a.7.7 0 0 1-1.08-.59V4.9a.9.9 0 0 1 .9-.9Z"/>
                </svg>
            </span>
            <span>Bookmark</span>
        </a>
        <a class="mobile-bottom-nav__item" href="<?= e($mobileLibraryRoute) ?>" aria-label="Library">
            <span class="mobile-bottom-nav__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <path d="M5.4 4.5h3.2a.9.9 0 0 1 .9.9v13.2a.9.9 0 0 1-.9.9H5.4a.9.9 0 0 1-.9-.9V5.4a.9.9 0 0 1 .9-.9Zm10 0h3.2a.9.9 0 0 1 .9.9v13.2a.9.9 0 0 1-.9.9h-3.2a.9.9 0 0 1-.9-.9V5.4a.9.9 0 0 1 .9-.9Zm-5 1.2h3.2a.9.9 0 0 1 .9.9v12a.9.9 0 0 1-.9.9h-3.2a.9.9 0 0 1-.9-.9v-12a.9.9 0 0 1 .9-.9Z"/>
                </svg>
            </span>
            <span>Library</span>
        </a>
        <a class="mobile-bottom-nav__item <?= str_starts_with($routeName, 'admin.') || in_array($routeName, ['login', 'register'], true) ? 'is-active' : '' ?>" href="<?= e($mobileAccountRoute) ?>" aria-label="<?= e($mobileAccountLabel) ?>">
            <span class="mobile-bottom-nav__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <path d="M12 4a4.4 4.4 0 1 1 0 8.8A4.4 4.4 0 0 1 12 4Zm0 10.6c4.58 0 7.4 2.08 7.4 4.1v.6a.7.7 0 0 1-.7.7H5.3a.7.7 0 0 1-.7-.7v-.6c0-2.02 2.82-4.1 7.4-4.1Z"/>
                </svg>
            </span>
            <span><?= e($mobileAccountLabel) ?></span>
        </a>
    </div>
</nav>
