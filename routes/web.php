<?php

declare(strict_types=1);

function validate_auth_form(string $mode): array
{
    $errors = [];
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

    if ($mode === 'register' && $name === '') {
        $errors[] = 'Nama wajib diisi.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email tidak valid.';
    }

    if ($password === '' || strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }

    if ($mode === 'register') {
        if (email_exists($email)) {
            $errors[] = 'Email sudah terdaftar.';
        }

        if ($password !== $passwordConfirmation) {
            $errors[] = 'Konfirmasi password tidak cocok.';
        }
    }

    return $errors;
}

function validate_manga_payload(?int $ignoreId = null): array
{
    $errors = [];
    $judul = trim((string) ($_POST['judul'] ?? ''));
    $slug = trim((string) ($_POST['slug'] ?? ''));
    if ($judul === '') {
        $errors[] = 'Judul komik wajib diisi.';
    }
    if ($slug === '') {
        $errors[] = 'Slug wajib diisi.';
    } elseif (slug_exists($slug, $ignoreId)) {
        $errors[] = 'Slug sudah dipakai komik lain.';
    }
    if (trim($_POST['source_base'] ?? '') === '') {
        $errors[] = 'Source base wajib diisi agar tetap kompatibel dengan scraper.';
    }

    return $errors;
}

function normalize_manga_payload(): array
{
    $genres = preg_split('/[\r\n,]+/', (string) ($_POST['genres'] ?? '')) ?: [];

    return [
        'slug' => trim((string) ($_POST['slug'] ?? '')),
        'judul' => trim((string) ($_POST['judul'] ?? '')),
        'manga_url' => trim((string) ($_POST['manga_url'] ?? '')),
        'external_manga_id' => trim((string) ($_POST['external_manga_id'] ?? '')) ?: null,
        'source_base' => trim((string) ($_POST['source_base'] ?? '')),
        'thumbnail_url' => trim((string) ($_POST['thumbnail_url'] ?? '')) ?: null,
        'description' => trim((string) ($_POST['description'] ?? '')) ?: null,
        'genres_json' => encode_genres_json($genres),
        'author' => trim((string) ($_POST['author'] ?? '')) ?: null,
        'artist' => trim((string) ($_POST['artist'] ?? '')) ?: null,
        'status' => trim((string) ($_POST['status'] ?? 'ongoing')) ?: 'ongoing',
    ];
}

function validate_chapter_payload(int $mangaId, ?int $ignoreId = null): array
{
    $errors = [];
    $chapterNumber = (int) ($_POST['chapter_number'] ?? 0);
    $chapterLabel = trim((string) ($_POST['chapter_label'] ?? ''));
    $chapterUrl = trim((string) ($_POST['chapter_url'] ?? ''));
    $images = parse_images_from_textarea((string) ($_POST['images'] ?? ''));

    if ($chapterNumber <= 0) {
        $errors[] = 'Nomor chapter harus lebih dari 0.';
    }
    if ($chapterLabel === '') {
        $errors[] = 'Label chapter wajib diisi.';
    }
    if ($chapterUrl === '') {
        $errors[] = 'URL chapter wajib diisi.';
    }
    if ($chapterNumber > 0 && chapter_number_exists($mangaId, $chapterNumber, $ignoreId)) {
        $errors[] = 'Nomor chapter sudah ada untuk komik ini.';
    }
    if ($images === []) {
        $errors[] = 'Minimal isi satu URL gambar CDN.';
    }

    return $errors;
}

function parse_images_from_textarea(string $text): array
{
    $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];

    return array_values(array_filter(array_map(static fn ($line) => trim($line), $lines)));
}

function normalize_chapter_payload(): array
{
    $images = parse_images_from_textarea((string) ($_POST['images'] ?? ''));

    return [
        'chapter_number' => (int) ($_POST['chapter_number'] ?? 0),
        'chapter_label' => trim((string) ($_POST['chapter_label'] ?? '')),
        'chapter_url' => trim((string) ($_POST['chapter_url'] ?? '')),
        'images' => $images,
    ];
}

switch ($route) {
    case 'image.proxy':
        $url = trim((string) ($_GET['url'] ?? ''));
        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            http_response_code(404);
            exit('Gambar tidak valid.');
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            http_response_code(400);
            exit('Skema URL tidak didukung.');
        }

        $cacheDir = dirname(__DIR__) . '/storage/cache/images';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $hash = md5($url);
        $bodyPath = $cacheDir . '/' . $hash . '.bin';
        $metaPath = $cacheDir . '/' . $hash . '.meta';
        $maxAge = 60 * 60 * 12;

        $serveCached = static function (string $filePath, string $fileMetaPath): never {
            $mime = is_file($fileMetaPath) ? trim((string) file_get_contents($fileMetaPath)) : 'image/jpeg';
            header('Content-Type: ' . ($mime !== '' ? $mime : 'image/jpeg'));
            header('Cache-Control: public, max-age=86400');
            readfile($filePath);
            exit;
        };

        if (is_file($bodyPath) && (time() - filemtime($bodyPath) < $maxAge)) {
            $serveCached($bodyPath, $metaPath);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0 Safari/537.36',
            CURLOPT_HTTPHEADER => [
                'Accept: image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                'Referer: https://05.ikiru.wtf/',
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $contentType = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if (is_string($body) && $body !== '' && $httpCode >= 200 && $httpCode < 300 && str_starts_with($contentType, 'image/')) {
            file_put_contents($bodyPath, $body);
            file_put_contents($metaPath, $contentType);
            $serveCached($bodyPath, $metaPath);
        }

        redirect('https://placehold.co/690x1000/111214/cfd3da?text=Komik');
        break;

    case 'search.ai':
        $query = trim((string) ($_GET['q'] ?? ''));
        $sort = trim((string) ($_GET['sort'] ?? 'popular')) ?: 'popular';
        $intent = build_ai_search_intent($query);
        $defaultSuggestions = ['Romance', 'Fantasy', 'Action', 'Drama', 'Ongoing'];

        if (mb_strlen($query) < 2) {
            $seed = paginate_mangas(
                current_user()['id'] ?? null,
                '',
                'popular',
                1,
                10,
                []
            );

            json_response([
                'query' => $query,
                'provider' => $intent['provider'] ?? 'local',
                'status_text' => 'Coba cari dengan bahasa bebas, misalnya "romance kerajaan cewek kuat".',
                'suggestions' => build_catalog_search_suggestions($seed['items'] ?? [], [], '', 5) ?: $defaultSuggestions,
                'items' => [],
            ]);
        }

        $result = paginate_mangas(
            current_user()['id'] ?? null,
            $query,
            $sort,
            1,
            5,
            $intent
        );

        $items = array_map(static function (array $manga): array {
            return [
                'judul' => (string) ($manga['judul'] ?? ''),
                'slug' => (string) ($manga['slug'] ?? ''),
                'url' => route_url('comic.show', ['slug' => $manga['slug'] ?? '']),
                'cover_url' => proxy_image_url($manga['cover_url'] ?? ''),
                'status' => (string) ($manga['status'] ?? 'ongoing'),
                'chapter_count' => (int) ($manga['chapter_count'] ?? 0),
                'genres' => array_values(array_slice($manga['genres'] ?? [], 0, 2)),
            ];
        }, $result['items']);

        $statusText = ($intent['summary'] ?? []) !== []
            ? 'Memahami: ' . implode(', ', $intent['summary'])
            : 'Memahami: ' . ai_search_normalize_query($query);

        json_response([
            'query' => $query,
            'provider' => $intent['provider'] ?? 'local',
            'status_text' => $statusText,
            'suggestions' => build_catalog_search_suggestions($result['items'] ?? [], $intent, $query, 5)
                ?: array_values(array_unique(array_merge($intent['suggestions'] ?? [], $defaultSuggestions))),
            'items' => $items,
        ]);
        break;

    case 'home':
        $searchQuery = trim((string) ($_GET['search'] ?? ''));
        $searchIntent = build_ai_search_intent($searchQuery);
        $perPage = home_catalog_per_page();
        $result = paginate_mangas(
            current_user()['id'] ?? null,
            $searchQuery,
            trim((string) ($_GET['sort'] ?? 'updated')),
            request_int('page', 1),
            $perPage,
            $searchIntent
        );
        render('home', [
            'result' => $result,
            'search' => $searchQuery,
            'sort' => trim((string) ($_GET['sort'] ?? 'updated')),
            'searchIntent' => $searchIntent,
            'perPage' => $perPage,
        ], 'Beranda');
        break;

    case 'comic.show':
        $slug = trim((string) ($_GET['slug'] ?? ''));
        $manga = find_manga_by_slug($slug, current_user()['id'] ?? null);
        if (!$manga) {
            http_response_code(404);
            exit('Komik tidak ditemukan.');
        }
        render('comic-show', ['manga' => $manga], $manga['judul']);
        break;

    case 'reader':
        $slug = trim((string) ($_GET['slug'] ?? ''));
        $chapterNumber = request_int('chapter');
        $chapter = find_chapter($slug, $chapterNumber);
        if (!$chapter) {
            http_response_code(404);
            exit('Chapter tidak ditemukan.');
        }
        render('reader', ['chapter' => $chapter], $chapter['judul'] . ' - ' . $chapter['chapter_label']);
        break;

    case 'login':
        require_guest();
        if (is_post()) {
            verify_csrf();
            remember_input($_POST);
            $errors = validate_auth_form('login');
            if ($errors !== []) {
                set_validation_errors($errors);
                redirect(route_url('login'));
            }

            $email = trim((string) $_POST['email']);
            $password = (string) $_POST['password'];
            if (!attempt_login($email, $password)) {
                set_validation_errors(['Email atau password salah.']);
                redirect(route_url('login'));
            }

            clear_form_state();
            flash('success', 'Login berhasil.');
            redirect(route_url('home'));
        }
        render('auth-login', [], 'Login');
        break;

    case 'register':
        require_guest();
        if (is_post()) {
            verify_csrf();
            remember_input($_POST);
            $errors = validate_auth_form('register');
            if ($errors !== []) {
                set_validation_errors($errors);
                redirect(route_url('register'));
            }

            create_user(
                trim((string) $_POST['name']),
                trim((string) $_POST['email']),
                (string) $_POST['password']
            );

            attempt_login(trim((string) $_POST['email']), (string) $_POST['password']);
            clear_form_state();
            flash('success', 'Akun berhasil dibuat.');
            redirect(route_url('home'));
        }
        render('auth-register', [], 'Register');
        break;

    case 'logout':
        require_login();
        if (is_post()) {
            verify_csrf();
            logout_user();
            flash('success', 'Logout berhasil.');
        }
        redirect(route_url('home'));
        break;

    case 'bookmarks':
        require_login();
        render('bookmarks', ['items' => user_bookmarks((int) current_user()['id'])], 'Bookmark');
        break;

    case 'comic.like':
        require_login();
        verify_csrf();
        $mangaId = request_int('manga_id');
        $liked = toggle_like((int) current_user()['id'], $mangaId);
        flash('success', $liked ? 'Komik disukai.' : 'Like dihapus.');
        redirect(back_url());
        break;

    case 'comic.bookmark':
        require_login();
        verify_csrf();
        $mangaId = request_int('manga_id');
        $bookmarked = toggle_bookmark((int) current_user()['id'], $mangaId);
        flash('success', $bookmarked ? 'Komik masuk bookmark.' : 'Bookmark dihapus.');
        redirect(back_url());
        break;

    case 'comic.vote':
        require_login();
        verify_csrf();
        $mangaId = request_int('manga_id');
        $voteType = trim((string) ($_POST['vote_type'] ?? ''));
        if (!in_array($voteType, ['continue', 'stop'], true)) {
            flash('error', 'Pilihan vote tidak valid.');
            redirect(back_url());
        }
        save_vote((int) current_user()['id'], $mangaId, $voteType);
        flash('success', 'Voting berhasil disimpan.');
        redirect(back_url());
        break;

    case 'admin.dashboard':
        require_admin();
        render('admin-dashboard', [
            'summary' => admin_dashboard_summary(),
            'mangas' => all_mangas_for_admin(),
        ], 'Admin Dashboard');
        break;

    case 'admin.manga.create':
        require_admin();
        if (is_post()) {
            verify_csrf();
            remember_input($_POST);
            $errors = validate_manga_payload();
            if ($errors !== []) {
                set_validation_errors($errors);
                redirect(route_url('admin.manga.create'));
            }
            create_manga(normalize_manga_payload());
            clear_form_state();
            flash('success', 'Komik baru berhasil dibuat.');
            redirect(route_url('admin.dashboard'));
        }
        render('admin-manga-form', ['mode' => 'create', 'manga' => null], 'Tambah Komik');
        break;

    case 'admin.manga.edit':
        require_admin();
        $manga = find_manga_by_id(request_int('id'));
        if (!$manga) {
            http_response_code(404);
            exit('Komik tidak ditemukan.');
        }
        if (is_post()) {
            verify_csrf();
            remember_input($_POST);
            $errors = validate_manga_payload((int) $manga['id']);
            if ($errors !== []) {
                set_validation_errors($errors);
                redirect(route_url('admin.manga.edit', ['id' => $manga['id']]));
            }
            update_manga((int) $manga['id'], normalize_manga_payload());
            clear_form_state();
            flash('success', 'Data komik berhasil diperbarui.');
            redirect(route_url('admin.dashboard'));
        }
        render('admin-manga-form', ['mode' => 'edit', 'manga' => $manga], 'Edit Komik');
        break;

    case 'admin.manga.delete':
        require_admin();
        verify_csrf();
        delete_manga(request_int('id'));
        flash('success', 'Komik berhasil dihapus.');
        redirect(route_url('admin.dashboard'));
        break;

    case 'admin.chapter.create':
        require_admin();
        $manga = find_manga_by_id(request_int('manga_id'));
        if (!$manga) {
            http_response_code(404);
            exit('Komik tidak ditemukan.');
        }
        if (is_post()) {
            verify_csrf();
            remember_input($_POST);
            $errors = validate_chapter_payload((int) $manga['id']);
            if ($errors !== []) {
                set_validation_errors($errors);
                redirect(route_url('admin.chapter.create', ['manga_id' => $manga['id']]));
            }
            create_chapter((int) $manga['id'], normalize_chapter_payload());
            clear_form_state();
            flash('success', 'Chapter berhasil ditambahkan.');
            redirect(route_url('comic.show', ['slug' => $manga['slug']]));
        }
        render('admin-chapter-form', ['mode' => 'create', 'manga' => $manga, 'chapter' => null], 'Tambah Chapter');
        break;

    case 'admin.chapter.edit':
        require_admin();
        $chapter = find_chapter_by_id(request_int('id'));
        if (!$chapter) {
            http_response_code(404);
            exit('Chapter tidak ditemukan.');
        }
        $manga = find_manga_by_id((int) $chapter['manga_id']);
        if (!$manga) {
            http_response_code(404);
            exit('Komik tidak ditemukan.');
        }
        if (is_post()) {
            verify_csrf();
            remember_input($_POST);
            $errors = validate_chapter_payload((int) $manga['id'], (int) $chapter['id']);
            if ($errors !== []) {
                set_validation_errors($errors);
                redirect(route_url('admin.chapter.edit', ['id' => $chapter['id']]));
            }
            update_chapter((int) $chapter['id'], normalize_chapter_payload());
            clear_form_state();
            flash('success', 'Chapter berhasil diperbarui.');
            redirect(route_url('comic.show', ['slug' => $manga['slug']]));
        }
        render('admin-chapter-form', ['mode' => 'edit', 'manga' => $manga, 'chapter' => $chapter], 'Edit Chapter');
        break;

    case 'admin.chapter.delete':
        require_admin();
        verify_csrf();
        delete_chapter(request_int('id'));
        flash('success', 'Chapter berhasil dihapus.');
        redirect(back_url('admin.dashboard'));
        break;

    default:
        http_response_code(404);
        exit('Route tidak ditemukan.');
}
