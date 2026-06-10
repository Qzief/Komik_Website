<?php

declare(strict_types=1);

function build_cover_sql(string $mangaAlias = 'm'): string
{
    $firstChapterImageSql = "
        (
            SELECT c0.first_image_url
            FROM chapters c0
            WHERE c0.manga_id = {$mangaAlias}.id
              AND c0.first_image_url IS NOT NULL
              AND c0.first_image_url <> ''
            ORDER BY c0.chapter_number ASC
            LIMIT 1
        )
    ";

    return "
        COALESCE(NULLIF({$mangaAlias}.thumbnail_url, ''), {$firstChapterImageSql})
    ";
}

function ai_search_normalize_query(string $query): string
{
    $query = mb_strtolower(trim($query));
    $query = strtr($query, [
        'romnace' => 'romance',
        'romace' => 'romance',
        'isekay' => 'isekai',
        'iseke' => 'isekai',
        'mangha' => 'manhwa',
        'mangwha' => 'manhwa',
        'kerajan' => 'kerajaan',
        'kerajaa' => 'kerajaan',
        'overpowe' => 'overpower',
        'over power' => 'overpower',
        'cewe' => 'cewek',
        'cwe' => 'cewek',
        'cowo' => 'cowok',
        'cwok' => 'cowok',
        'reinkarnsi' => 'reinkarnasi',
    ]);
    $query = preg_replace('/[^\p{L}\p{N}\s-]+/u', ' ', $query) ?? $query;
    $query = preg_replace('/\s+/u', ' ', $query) ?? $query;

    return trim($query);
}

function merge_ai_search_intent(array $baseIntent, array $remoteIntent): array
{
    $merged = $baseIntent;

    foreach (['matches', 'genres', 'moods', 'tags', 'tokens', 'term_pool', 'suggestions', 'summary'] as $key) {
        $merged[$key] = array_values(array_unique(array_filter(array_merge(
            $baseIntent[$key] ?? [],
            is_array($remoteIntent[$key] ?? null) ? $remoteIntent[$key] : []
        ), static fn ($value) => is_string($value) && trim($value) !== '')));
    }

    if (($baseIntent['status'] ?? null) === null && is_string($remoteIntent['status'] ?? null) && $remoteIntent['status'] !== '') {
        $merged['status'] = $remoteIntent['status'];
    }

    if (is_string($remoteIntent['normalized'] ?? null) && trim($remoteIntent['normalized']) !== '') {
        $merged['normalized'] = trim($remoteIntent['normalized']);
    }

    $merged['is_ai'] = ($baseIntent['is_ai'] ?? false) || ($remoteIntent['is_ai'] ?? false);
    $merged['provider'] = $remoteIntent['provider'] ?? ($baseIntent['provider'] ?? 'local');

    return $merged;
}

function parse_json_object_from_text(string $text): ?array
{
    $text = trim($text);
    if ($text === '') {
        return null;
    }

    $decoded = json_decode($text, true);
    if (is_array($decoded)) {
        return $decoded;
    }

    $start = strpos($text, '{');
    $end = strrpos($text, '}');
    if ($start === false || $end === false || $end <= $start) {
        return null;
    }

    $snippet = substr($text, $start, $end - $start + 1);
    $decoded = json_decode($snippet, true);

    return is_array($decoded) ? $decoded : null;
}

function nvidia_ai_search_intent(string $query): ?array
{
    $apiKey = trim((string) config('ai.nvidia_api_key'));
    $invokeUrl = trim((string) config('ai.nvidia_invoke_url'));
    $model = trim((string) config('ai.nvidia_model'));

    if ($query === '' || $apiKey === '' || $invokeUrl === '' || $model === '' || !function_exists('curl_init')) {
        return null;
    }

    $cacheDir = dirname(__DIR__) . '/storage/cache/ai-search';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0777, true);
    }

    $cacheKey = sha1($model . '|' . ai_search_normalize_query($query));
    $cachePath = $cacheDir . '/' . $cacheKey . '.json';
    if (is_file($cachePath) && (time() - filemtime($cachePath) < 60 * 60 * 12)) {
        $cached = json_decode((string) file_get_contents($cachePath), true);
        if (is_array($cached)) {
            return $cached;
        }
    }

    $payload = [
        'model' => $model,
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are an intent parser for a comic and manhwa website search. Return JSON only. No markdown. Convert natural-language Indonesian or English search into concise search intent.',
            ],
            [
                'role' => 'user',
                'content' => "Ubah query pencarian komik ini menjadi JSON dengan schema:\n" .
                    "{\"normalized\":\"string\",\"genres\":[\"...\"],\"tags\":[\"...\"],\"moods\":[\"...\"],\"status\":\"ongoing|completed|null\",\"term_pool\":[\"...\"],\"summary\":[\"...\"],\"suggestions\":[\"...\"],\"is_ai\":true}\n" .
                    "Gunakan istilah ringkas yang relevan untuk website komik/manhwa. Maksimal 3 genres, 4 tags/moods, 6 term_pool, 3 suggestions. Query: " . $query,
            ],
        ],
        'temperature' => 0.2,
        'top_p' => 0.7,
        'frequency_penalty' => 0,
        'presence_penalty' => 0,
        'max_tokens' => 1024,
        'stream' => false,
    ];

    $ch = curl_init($invokeUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Accept: application/json',
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);

    $responseBody = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if (!is_string($responseBody) || $responseBody === '' || $httpCode < 200 || $httpCode >= 300) {
        return null;
    }

    $responseJson = json_decode($responseBody, true);
    if (!is_array($responseJson)) {
        return null;
    }

    $content = (string) ($responseJson['choices'][0]['message']['content'] ?? '');
    $decodedIntent = parse_json_object_from_text($content);
    if (!is_array($decodedIntent)) {
        return null;
    }

    $intent = [
        'normalized' => trim((string) ($decodedIntent['normalized'] ?? ai_search_normalize_query($query))),
        'genres' => array_values(array_filter(array_map('trim', $decodedIntent['genres'] ?? []))),
        'tags' => array_values(array_filter(array_map('trim', $decodedIntent['tags'] ?? []))),
        'moods' => array_values(array_filter(array_map('trim', $decodedIntent['moods'] ?? []))),
        'status' => in_array(($decodedIntent['status'] ?? null), ['ongoing', 'completed'], true) ? $decodedIntent['status'] : null,
        'term_pool' => array_values(array_filter(array_map('trim', $decodedIntent['term_pool'] ?? []))),
        'summary' => array_values(array_filter(array_map('trim', $decodedIntent['summary'] ?? []))),
        'suggestions' => array_values(array_filter(array_map('trim', $decodedIntent['suggestions'] ?? []))),
        'is_ai' => true,
        'provider' => 'nvidia',
    ];

    file_put_contents($cachePath, json_encode($intent, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    return $intent;
}

function build_ai_search_intent(string $query): array
{
    $query = trim($query);
    $normalized = ai_search_normalize_query($query);
    $tokens = $normalized === '' ? [] : (preg_split('/\s+/u', $normalized) ?: []);

    $conceptMap = [
        'romance' => ['romance', 'romantis'],
        'fantasy' => ['fantasy', 'fantasi'],
        'action' => ['action', 'aksi'],
        'comedy' => ['comedy', 'komedi', 'lucu'],
        'isekai' => ['isekai', 'dunia lain'],
        'kerajaan' => ['kerajaan', 'kingdom', 'bangsawan', 'noble', 'duke', 'princess', 'villainess'],
        'reinkarnasi' => ['reinkarnasi', 'reincarnation', 'hidup lagi', 'lahir kembali'],
        'cewek kuat' => ['cewek kuat', 'perempuan kuat', 'wanita kuat', 'female lead kuat', 'mc cewek kuat'],
        'cowok dingin' => ['cowok dingin', 'pria dingin', 'male lead dingin', 'ml dingin', 'cold male lead'],
        'overpower' => ['overpower', 'op mc', 'overpowered', 'mc kuat', 'mc diremehkan'],
        'school life' => ['school life', 'sekolah', 'anak sekolah'],
        'cute' => ['cute', 'imut', 'manis'],
        'sad' => ['sad', 'sedih', 'tragis'],
        'wholesome' => ['wholesome', 'hangat'],
        'completed' => ['completed', 'tamat', 'selesai'],
        'ongoing' => ['ongoing', 'lanjut', 'berjalan'],
        'manhwa' => ['manhwa'],
        'manga' => ['manga'],
        'mirip solo leveling' => ['mirip solo leveling', 'vibe solo leveling', 'kayak solo leveling'],
    ];

    $matches = [];
    foreach ($conceptMap as $canonical => $variants) {
        foreach ($variants as $variant) {
            if (str_contains($normalized, $variant)) {
                $matches[] = $canonical;
                break;
            }
        }
    }

    $matches = array_values(array_unique($matches));
    $genres = array_values(array_intersect($matches, ['romance', 'fantasy', 'action', 'comedy', 'school life']));
    $moods = array_values(array_intersect($matches, ['cute', 'sad', 'wholesome', 'overpower', 'cewek kuat', 'cowok dingin']));
    $tags = array_values(array_intersect($matches, ['isekai', 'kerajaan', 'reinkarnasi', 'manhwa', 'manga', 'mirip solo leveling']));
    $status = in_array('completed', $matches, true) ? 'completed' : (in_array('ongoing', $matches, true) ? 'ongoing' : null);

    $termPool = array_values(array_unique(array_filter(array_merge(
        $tokens,
        $genres,
        $moods,
        $tags,
        $status ? [$status] : []
    ), static fn ($token) => mb_strlen((string) $token) >= 3)));

    $summaryParts = [];
    if ($genres !== []) {
        $summaryParts[] = implode(', ', $genres);
    }
    if ($tags !== []) {
        $summaryParts[] = implode(', ', $tags);
    }
    if ($moods !== []) {
        $summaryParts[] = implode(', ', $moods);
    }
    if ($status !== null) {
        $summaryParts[] = $status === 'completed' ? 'status tamat' : 'status ongoing';
    }

    $suggestions = [];
    if (in_array('romance', $genres, true) && in_array('kerajaan', $tags, true)) {
        $suggestions = ['villainess reincarnation', 'romance kerajaan tamat', 'cewek kuat dunia bangsawan'];
    } elseif (in_array('action', $genres, true) || in_array('overpower', $moods, true)) {
        $suggestions = ['manhwa action overpower', 'mc diremehkan jadi kuat', 'mirip solo leveling'];
    } elseif ($query !== '') {
        $suggestions = ['romance kerajaan cewek kuat', 'isekai lucu yang ringan', 'manhwa action murid lemah jadi overpower'];
    }

    $localIntent = [
        'query' => $query,
        'normalized' => $normalized,
        'tokens' => $tokens,
        'matches' => $matches,
        'genres' => $genres,
        'moods' => $moods,
        'tags' => $tags,
        'status' => $status,
        'term_pool' => $termPool,
        'summary' => $summaryParts,
        'suggestions' => $suggestions,
        'is_ai' => $query !== '' && (count($matches) > 0 || count($tokens) > 1),
        'provider' => 'local',
    ];

    $remoteIntent = nvidia_ai_search_intent($query);

    return $remoteIntent ? merge_ai_search_intent($localIntent, $remoteIntent) : $localIntent;
}

function score_manga_against_intent(array $manga, array $intent): int
{
    $title = mb_strtolower((string) ($manga['judul'] ?? ''));
    $slug = mb_strtolower((string) ($manga['slug'] ?? ''));
    $description = mb_strtolower((string) ($manga['description'] ?? ''));
    $status = mb_strtolower((string) ($manga['status'] ?? ''));
    $genres = array_map(static fn ($genre) => mb_strtolower((string) $genre), $manga['genres'] ?? []);

    $score = 0;

    foreach ($intent['term_pool'] ?? [] as $term) {
        $term = mb_strtolower((string) $term);
        if ($term === '') {
            continue;
        }

        if (str_contains($title, $term)) {
            $score += 14;
        }
        if (str_contains($slug, $term)) {
            $score += 9;
        }
        if (str_contains($description, $term)) {
            $score += 6;
        }
        foreach ($genres as $genre) {
            if (str_contains($genre, $term)) {
                $score += 11;
                break;
            }
        }
        if (str_contains($status, $term)) {
            $score += 7;
        }
    }

    if (($intent['status'] ?? null) !== null && $status === $intent['status']) {
        $score += 10;
    }

    if (($intent['genres'] ?? []) !== []) {
        $matchedGenres = 0;
        foreach ($intent['genres'] as $genre) {
            if (in_array($genre, $genres, true) || str_contains($description, $genre)) {
                $matchedGenres++;
            }
        }
        $score += $matchedGenres * 10;
    }

    if (($intent['tags'] ?? []) !== []) {
        foreach ($intent['tags'] as $tag) {
            if (str_contains($description, $tag) || str_contains($title, $tag) || str_contains($slug, $tag)) {
                $score += 8;
            }
        }
    }

    return $score;
}

function format_search_chip_label(string $value): string
{
    $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    if ($value === '') {
        return '';
    }

    $map = [
        'romance' => 'Romance',
        'fantasy' => 'Fantasy',
        'action' => 'Action',
        'comedy' => 'Comedy',
        'school life' => 'School Life',
        'isekai' => 'Isekai',
        'kerajaan' => 'Kerajaan',
        'reinkarnasi' => 'Reinkarnasi',
        'cewek kuat' => 'Cewek Kuat',
        'cowok dingin' => 'Cowok Dingin',
        'cute' => 'Cute',
        'wholesome' => 'Wholesome',
        'sad' => 'Sad',
        'overpower' => 'Overpower',
        'ongoing' => 'Ongoing',
        'completed' => 'Tamat',
        'tamat' => 'Tamat',
        'manhwa' => 'Manhwa',
        'manga' => 'Manga',
        'seinen' => 'Seinen',
        'drama' => 'Drama',
        'adventure' => 'Adventure',
        'slice of life' => 'Slice of Life',
    ];

    $normalized = mb_strtolower($value);
    if (isset($map[$normalized])) {
        return $map[$normalized];
    }

    $parts = preg_split('/\s+/u', $normalized) ?: [];

    return implode(' ', array_map(static fn (string $part): string => mb_convert_case($part, MB_CASE_TITLE, 'UTF-8'), $parts));
}

function build_catalog_search_suggestions(array $items, array $intent = [], string $query = '', int $limit = 5): array
{
    $pairCounts = [];
    $genreCounts = [];
    $statusGenreCounts = [];
    $titleSuggestions = [];

    foreach ($items as $item) {
        $genres = array_values(array_filter(array_map(
            static fn ($genre) => mb_strtolower(trim((string) $genre)),
            $item['genres'] ?? []
        )));
        $status = mb_strtolower(trim((string) ($item['status'] ?? '')));

        if (count($genres) >= 2) {
            $pairLabel = format_search_chip_label($genres[0]) . ' ' . format_search_chip_label($genres[1]);
            $pairCounts[$pairLabel] = ($pairCounts[$pairLabel] ?? 0) + 1;
        }

        foreach ($genres as $genre) {
            $genreLabel = format_search_chip_label($genre);
            if ($genreLabel === '') {
                continue;
            }

            $genreCounts[$genreLabel] = ($genreCounts[$genreLabel] ?? 0) + 1;

            if (in_array($status, ['ongoing', 'completed'], true)) {
                $statusLabel = $status === 'completed' ? 'Tamat' : 'Ongoing';
                $comboLabel = $genreLabel . ' ' . $statusLabel;
                $statusGenreCounts[$comboLabel] = ($statusGenreCounts[$comboLabel] ?? 0) + 1;
            }
        }

        $title = trim((string) ($item['judul'] ?? ''));
        if ($title !== '' && mb_strlen($title) <= 28) {
            $titleSuggestions[$title] = ($titleSuggestions[$title] ?? 0) + 1;
        }
    }

    arsort($pairCounts);
    arsort($statusGenreCounts);
    arsort($genreCounts);
    arsort($titleSuggestions);

    $suggestions = [];

    foreach (array_keys($pairCounts) as $label) {
        $suggestions[] = $label;
    }

    foreach (array_keys($statusGenreCounts) as $label) {
        $suggestions[] = $label;
    }

    foreach (array_keys($genreCounts) as $label) {
        $suggestions[] = $label;
    }

    foreach (array_keys($titleSuggestions) as $label) {
        $suggestions[] = $label;
    }

    $intentLabels = array_merge(
        array_map('format_search_chip_label', $intent['genres'] ?? []),
        array_map('format_search_chip_label', $intent['tags'] ?? []),
        array_map('format_search_chip_label', $intent['moods'] ?? [])
    );

    if (($intent['status'] ?? null) === 'completed') {
        $intentLabels[] = 'Tamat';
    } elseif (($intent['status'] ?? null) === 'ongoing') {
        $intentLabels[] = 'Ongoing';
    }

    if ($query !== '') {
        $suggestions = array_merge($suggestions, $intentLabels);
    }

    $suggestions = array_values(array_unique(array_filter(array_map(
        static fn ($label) => trim((string) $label),
        $suggestions
    ))));

    return array_slice($suggestions, 0, $limit);
}

function parse_genres_json(mixed $value): array
{
    if (!is_string($value) || trim($value) === '') {
        return [];
    }

    $decoded = json_decode($value, true);
    if (!is_array($decoded)) {
        return [];
    }

    return array_values(array_filter(array_map(
        static fn ($genre) => is_string($genre) ? trim($genre) : '',
        $decoded
    )));
}

function encode_genres_json(array $genres): ?string
{
    $genres = array_values(array_unique(array_filter(array_map(
        static fn ($genre) => trim((string) $genre),
        $genres
    ))));

    return $genres === [] ? null : json_encode($genres, JSON_UNESCAPED_UNICODE);
}

function hydrate_manga_row(array $manga): array
{
    $manga['genres'] = parse_genres_json($manga['genres_json'] ?? null);

    return $manga;
}

function manga_list_select_sql(?int $userId): string
{
    return "
        SELECT
            m.*,
            " . build_cover_sql('m') . " AS cover_url,
            COUNT(DISTINCT cl.id) AS like_count,
            COUNT(DISTINCT cb.id) AS bookmark_count,
            COALESCE(vv.continue_count, 0) AS continue_count,
            COALESCE(vv.stop_count, 0) AS stop_count,
            " . ($userId ? 'MAX(CASE WHEN cl.user_id = :user_like_id THEN 1 ELSE 0 END)' : '0') . " AS is_liked,
            " . ($userId ? 'MAX(CASE WHEN cb.user_id = :user_bookmark_id THEN 1 ELSE 0 END)' : '0') . " AS is_bookmarked
        FROM mangas m
        LEFT JOIN comic_likes cl ON cl.manga_id = m.id
        LEFT JOIN comic_bookmarks cb ON cb.manga_id = m.id
        LEFT JOIN (
            SELECT
                manga_id,
                SUM(vote_type = 'continue') AS continue_count,
                SUM(vote_type = 'stop') AS stop_count
            FROM comic_votes
            GROUP BY manga_id
        ) vv ON vv.manga_id = m.id
    ";
}

function manga_list_bindings(?int $userId, string $search, array $intent = []): array
{
    $bindings = [];

    if ($search !== '') {
        $bindings['search_phrase'] = '%' . ai_search_normalize_query($search) . '%';

        foreach (array_values($intent['term_pool'] ?? []) as $index => $term) {
            $bindings['term_' . $index] = '%' . $term . '%';
        }
    }

    if ($userId) {
        $bindings['user_like_id'] = $userId;
        $bindings['user_bookmark_id'] = $userId;
    }

    return $bindings;
}

function manga_list_where_sql(string $search, array $intent = []): string
{
    if ($search === '') {
        return '';
    }

    $clauses = [
        'm.judul LIKE :search_phrase',
        'm.slug LIKE :search_phrase',
        'COALESCE(m.description, \'\') LIKE :search_phrase',
        'COALESCE(m.genres_json, \'\') LIKE :search_phrase',
    ];

    foreach (array_keys($intent['term_pool'] ?? []) as $index) {
        $termBinding = ':term_' . $index;
        $clauses[] = "(m.judul LIKE {$termBinding} OR m.slug LIKE {$termBinding} OR COALESCE(m.description, '') LIKE {$termBinding} OR COALESCE(m.genres_json, '') LIKE {$termBinding} OR COALESCE(m.status, '') LIKE {$termBinding})";
    }

    return 'WHERE (' . implode(' OR ', $clauses) . ')';
}

function manga_list_order_sql(string $sort): string
{
    $allowedSort = [
        'updated' => 'm.updated_at DESC',
        'popular' => 'like_count DESC, bookmark_count DESC, m.updated_at DESC',
        'title' => 'm.judul ASC',
    ];

    return $allowedSort[$sort] ?? $allowedSort['updated'];
}

function featured_manga(?int $userId, string $search = '', string $sort = 'updated', array $intent = []): ?array
{
    $where = manga_list_where_sql($search, $intent);
    $orderBy = manga_list_order_sql($sort);
    $bindings = manga_list_bindings($userId, $search, $intent);

    $sql = manga_list_select_sql($userId) . "
        {$where}
        GROUP BY m.id
        ORDER BY {$orderBy}
        LIMIT 1
    ";

    $manga = db_query($sql, $bindings)->fetch();

    return $manga ? hydrate_manga_row($manga) : null;
}

function paginate_mangas(?int $userId, string $search = '', string $sort = 'updated', int $page = 1, int $perPage = 24, array $intent = []): array
{
    $page = max(1, $page);
    $orderBy = manga_list_order_sql($sort);
    $where = manga_list_where_sql($search, $intent);
    $bindings = manga_list_bindings($userId, $search, $intent);

    if ($search !== '') {
        $sql = manga_list_select_sql($userId) . "
            {$where}
            GROUP BY m.id
            ORDER BY m.updated_at DESC
        ";

        $matchedItems = array_map('hydrate_manga_row', db_query($sql, $bindings)->fetchAll());

        usort($matchedItems, static function (array $left, array $right) use ($intent, $sort): int {
            $scoreDiff = score_manga_against_intent($right, $intent) <=> score_manga_against_intent($left, $intent);
            if ($scoreDiff !== 0) {
                return $scoreDiff;
            }

            if ($sort === 'title') {
                return strcmp((string) ($left['judul'] ?? ''), (string) ($right['judul'] ?? ''));
            }

            if ($sort === 'popular') {
                $leftPopularity = (int) ($left['like_count'] ?? 0) + (int) ($left['bookmark_count'] ?? 0);
                $rightPopularity = (int) ($right['like_count'] ?? 0) + (int) ($right['bookmark_count'] ?? 0);
                if ($rightPopularity !== $leftPopularity) {
                    return $rightPopularity <=> $leftPopularity;
                }
            }

            return strcmp((string) ($right['updated_at'] ?? ''), (string) ($left['updated_at'] ?? ''));
        });

        $total = count($matchedItems);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);
        $offset = ($page - 1) * $perPage;
        $items = array_slice($matchedItems, $offset, $perPage);

        return [
            'items' => $items,
            'featured' => $items[0] ?? null,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => $lastPage,
        ];
    }

    $countSql = "SELECT COUNT(*) FROM mangas m {$where}";
    $total = (int) db_query($countSql, [])->fetchColumn();
    $lastPage = max(1, (int) ceil($total / $perPage));
    $page = min($page, $lastPage);
    $offset = ($page - 1) * $perPage;

    $sql = manga_list_select_sql($userId) . "
        {$where}
        GROUP BY m.id
        ORDER BY {$orderBy}
        LIMIT {$perPage} OFFSET {$offset}
    ";

    $items = array_map('hydrate_manga_row', db_query($sql, $bindings)->fetchAll());

    return [
        'items' => $items,
        'featured' => featured_manga($userId, $search, $sort, $intent),
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'last_page' => $lastPage,
    ];
}

function find_manga_by_slug(string $slug, ?int $userId = null): ?array
{
    $sql = "
        SELECT
            m.*,
            " . build_cover_sql('m') . " AS cover_url,
            COUNT(DISTINCT cl.id) AS like_count,
            COUNT(DISTINCT cb.id) AS bookmark_count,
            COALESCE(vv.continue_count, 0) AS continue_count,
            COALESCE(vv.stop_count, 0) AS stop_count,
            " . ($userId ? 'MAX(CASE WHEN cl.user_id = :user_like_id THEN 1 ELSE 0 END)' : '0') . " AS is_liked,
            " . ($userId ? 'MAX(CASE WHEN cb.user_id = :user_bookmark_id THEN 1 ELSE 0 END)' : '0') . " AS is_bookmarked,
            " . ($userId ? 'MAX(CASE WHEN cv.user_id = :user_vote_id THEN cv.vote_type ELSE NULL END)' : 'NULL') . " AS current_vote
        FROM mangas m
        LEFT JOIN comic_likes cl ON cl.manga_id = m.id
        LEFT JOIN comic_bookmarks cb ON cb.manga_id = m.id
        LEFT JOIN comic_votes cv ON cv.manga_id = m.id
        LEFT JOIN (
            SELECT
                manga_id,
                SUM(vote_type = 'continue') AS continue_count,
                SUM(vote_type = 'stop') AS stop_count
            FROM comic_votes
            GROUP BY manga_id
        ) vv ON vv.manga_id = m.id
        WHERE m.slug = :slug
        GROUP BY m.id
        LIMIT 1
    ";

    $params = ['slug' => $slug];
    if ($userId) {
        $params['user_like_id'] = $userId;
        $params['user_bookmark_id'] = $userId;
        $params['user_vote_id'] = $userId;
    }

    $manga = db_query($sql, $params)->fetch();
    if (!$manga) {
        return null;
    }

    $manga['chapters'] = db_query(
        'SELECT * FROM chapters WHERE manga_id = :manga_id ORDER BY chapter_number DESC',
        ['manga_id' => $manga['id']]
    )->fetchAll();

    return hydrate_manga_row($manga);
}

function find_chapter(string $slug, int $chapterNumber): ?array
{
    $chapter = db_query(
        "
        SELECT
            c.*,
            m.slug,
            m.judul,
            m.thumbnail_url,
            " . build_cover_sql('m') . " AS cover_url
        FROM chapters c
        INNER JOIN mangas m ON m.id = c.manga_id
        WHERE m.slug = :slug AND c.chapter_number = :chapter_number
        LIMIT 1
        ",
        ['slug' => $slug, 'chapter_number' => $chapterNumber]
    )->fetch();

    if (!$chapter) {
        return null;
    }

    $chapter['images'] = db_query(
        'SELECT image_order, image_url FROM chapter_images WHERE chapter_id = :chapter_id ORDER BY image_order ASC',
        ['chapter_id' => $chapter['id']]
    )->fetchAll();

    $chapter['prev_chapter'] = db_query(
        '
        SELECT chapter_number
        FROM chapters
        WHERE manga_id = :manga_id AND chapter_number < :chapter_number
        ORDER BY chapter_number DESC
        LIMIT 1
        ',
        ['manga_id' => $chapter['manga_id'], 'chapter_number' => $chapterNumber]
    )->fetchColumn();

    $chapter['next_chapter'] = db_query(
        '
        SELECT chapter_number
        FROM chapters
        WHERE manga_id = :manga_id AND chapter_number > :chapter_number
        ORDER BY chapter_number ASC
        LIMIT 1
        ',
        ['manga_id' => $chapter['manga_id'], 'chapter_number' => $chapterNumber]
    )->fetchColumn();

    $chapter['chapter_list'] = db_query(
        '
        SELECT chapter_number, chapter_label
        FROM chapters
        WHERE manga_id = :manga_id
        ORDER BY chapter_number DESC
        ',
        ['manga_id' => $chapter['manga_id']]
    )->fetchAll();

    return $chapter;
}

function user_bookmarks(int $userId): array
{
    return array_map('hydrate_manga_row', db_query(
        "
        SELECT
            m.*,
            " . build_cover_sql('m') . " AS cover_url,
            cb.created_at AS bookmarked_at
        FROM comic_bookmarks cb
        INNER JOIN mangas m ON m.id = cb.manga_id
        WHERE cb.user_id = :user_id
        ORDER BY cb.created_at DESC
        ",
        ['user_id' => $userId]
    )->fetchAll());
}

function admin_dashboard_summary(): array
{
    return [
        'total_manga' => (int) db_query('SELECT COUNT(*) FROM mangas')->fetchColumn(),
        'total_chapter' => (int) db_query('SELECT COUNT(*) FROM chapters')->fetchColumn(),
        'total_user' => (int) db_query('SELECT COUNT(*) FROM users')->fetchColumn(),
        'total_vote' => (int) db_query('SELECT COUNT(*) FROM comic_votes')->fetchColumn(),
    ];
}

function create_user(string $name, string $email, string $password, string $role = 'user'): void
{
    db_query(
        '
        INSERT INTO users (name, email, password, role)
        VALUES (:name, :email, :password, :role)
        ',
        [
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'role' => $role,
        ]
    );
}

function email_exists(string $email): bool
{
    return (bool) db_query('SELECT COUNT(*) FROM users WHERE email = :email', ['email' => $email])->fetchColumn();
}

function slug_exists(string $slug, ?int $ignoreId = null): bool
{
    $sql = 'SELECT COUNT(*) FROM mangas WHERE slug = :slug';
    $params = ['slug' => $slug];

    if ($ignoreId) {
        $sql .= ' AND id != :ignore_id';
        $params['ignore_id'] = $ignoreId;
    }

    return (bool) db_query($sql, $params)->fetchColumn();
}

function chapter_number_exists(int $mangaId, int $chapterNumber, ?int $ignoreId = null): bool
{
    $sql = 'SELECT COUNT(*) FROM chapters WHERE manga_id = :manga_id AND chapter_number = :chapter_number';
    $params = ['manga_id' => $mangaId, 'chapter_number' => $chapterNumber];

    if ($ignoreId) {
        $sql .= ' AND id != :ignore_id';
        $params['ignore_id'] = $ignoreId;
    }

    return (bool) db_query($sql, $params)->fetchColumn();
}

function toggle_like(int $userId, int $mangaId): bool
{
    $exists = db_query(
        'SELECT id FROM comic_likes WHERE user_id = :user_id AND manga_id = :manga_id LIMIT 1',
        ['user_id' => $userId, 'manga_id' => $mangaId]
    )->fetchColumn();

    if ($exists) {
        db_query('DELETE FROM comic_likes WHERE id = :id', ['id' => $exists]);

        return false;
    }

    db_query(
        'INSERT INTO comic_likes (user_id, manga_id) VALUES (:user_id, :manga_id)',
        ['user_id' => $userId, 'manga_id' => $mangaId]
    );

    return true;
}

function toggle_bookmark(int $userId, int $mangaId): bool
{
    $exists = db_query(
        'SELECT id FROM comic_bookmarks WHERE user_id = :user_id AND manga_id = :manga_id LIMIT 1',
        ['user_id' => $userId, 'manga_id' => $mangaId]
    )->fetchColumn();

    if ($exists) {
        db_query('DELETE FROM comic_bookmarks WHERE id = :id', ['id' => $exists]);

        return false;
    }

    db_query(
        'INSERT INTO comic_bookmarks (user_id, manga_id) VALUES (:user_id, :manga_id)',
        ['user_id' => $userId, 'manga_id' => $mangaId]
    );

    return true;
}

function save_vote(int $userId, int $mangaId, string $voteType): void
{
    db_query(
        '
        INSERT INTO comic_votes (user_id, manga_id, vote_type)
        VALUES (:user_id, :manga_id, :vote_type)
        ON DUPLICATE KEY UPDATE vote_type = VALUES(vote_type)
        ',
        [
            'user_id' => $userId,
            'manga_id' => $mangaId,
            'vote_type' => $voteType,
        ]
    );
}

function all_mangas_for_admin(): array
{
    return array_map('hydrate_manga_row', db_query(
        "
        SELECT
            m.*,
            " . build_cover_sql('m') . " AS cover_url,
            COUNT(c.id) AS real_chapter_count
        FROM mangas m
        LEFT JOIN chapters c ON c.manga_id = m.id
        GROUP BY m.id
        ORDER BY m.updated_at DESC
        "
    )->fetchAll());
}

function find_manga_by_id(int $id): ?array
{
    $manga = db_query(
        "
        SELECT
            m.*,
            " . build_cover_sql('m') . " AS cover_url
        FROM mangas m
        WHERE m.id = :id
        LIMIT 1
        ",
        ['id' => $id]
    )->fetch();

    return $manga ? hydrate_manga_row($manga) : null;
}

function create_manga(array $data): int
{
    db_query(
        '
        INSERT INTO mangas (
            slug,
            judul,
            manga_url,
            external_manga_id,
            source_base,
            thumbnail_url,
            chapter_count,
            description,
            genres_json,
            author,
            artist,
            status,
            last_scraped_at
        ) VALUES (
            :slug,
            :judul,
            :manga_url,
            :external_manga_id,
            :source_base,
            :thumbnail_url,
            0,
            :description,
            :genres_json,
            :author,
            :artist,
            :status,
            NOW()
        )
        ',
        $data
    );

    return (int) db()->lastInsertId();
}

function update_manga(int $id, array $data): void
{
    $data['id'] = $id;

    db_query(
        '
        UPDATE mangas
        SET slug = :slug,
            judul = :judul,
            manga_url = :manga_url,
            external_manga_id = :external_manga_id,
            source_base = :source_base,
            thumbnail_url = :thumbnail_url,
            description = :description,
            genres_json = :genres_json,
            author = :author,
            artist = :artist,
            status = :status
        WHERE id = :id
        ',
        $data
    );
}

function delete_manga(int $id): void
{
    db_query('DELETE FROM mangas WHERE id = :id', ['id' => $id]);
}

function sync_manga_chapter_count(int $mangaId): void
{
    db_query(
        '
        UPDATE mangas
        SET chapter_count = (SELECT COUNT(*) FROM chapters WHERE manga_id = :manga_id)
        WHERE id = :manga_id
        ',
        ['manga_id' => $mangaId]
    );
}

function create_chapter(int $mangaId, array $data): int
{
    db_query(
        '
        INSERT INTO chapters (
            manga_id,
            chapter_number,
            chapter_label,
            chapter_url,
            image_count,
            first_image_url
        ) VALUES (
            :manga_id,
            :chapter_number,
            :chapter_label,
            :chapter_url,
            :image_count,
            :first_image_url
        )
        ',
        [
            'manga_id' => $mangaId,
            'chapter_number' => $data['chapter_number'],
            'chapter_label' => $data['chapter_label'],
            'chapter_url' => $data['chapter_url'],
            'image_count' => count($data['images']),
            'first_image_url' => $data['images'][0] ?? null,
        ]
    );

    $chapterId = (int) db()->lastInsertId();
    replace_chapter_images($chapterId, $data['images']);
    sync_manga_chapter_count($mangaId);

    return $chapterId;
}

function update_chapter(int $chapterId, array $data): void
{
    db_query(
        '
        UPDATE chapters
        SET chapter_number = :chapter_number,
            chapter_label = :chapter_label,
            chapter_url = :chapter_url,
            image_count = :image_count,
            first_image_url = :first_image_url
        WHERE id = :id
        ',
        [
            'id' => $chapterId,
            'chapter_number' => $data['chapter_number'],
            'chapter_label' => $data['chapter_label'],
            'chapter_url' => $data['chapter_url'],
            'image_count' => count($data['images']),
            'first_image_url' => $data['images'][0] ?? null,
        ]
    );

    replace_chapter_images($chapterId, $data['images']);
}

function replace_chapter_images(int $chapterId, array $images): void
{
    db_query('DELETE FROM chapter_images WHERE chapter_id = :chapter_id', ['chapter_id' => $chapterId]);
    foreach (array_values($images) as $index => $imageUrl) {
        db_query(
            '
            INSERT INTO chapter_images (chapter_id, image_order, image_url)
            VALUES (:chapter_id, :image_order, :image_url)
            ',
            [
                'chapter_id' => $chapterId,
                'image_order' => $index + 1,
                'image_url' => $imageUrl,
            ]
        );
    }
}

function find_chapter_by_id(int $chapterId): ?array
{
    $chapter = db_query('SELECT * FROM chapters WHERE id = :id LIMIT 1', ['id' => $chapterId])->fetch();
    if (!$chapter) {
        return null;
    }

    $chapter['images'] = array_column(
        db_query(
            'SELECT image_url FROM chapter_images WHERE chapter_id = :chapter_id ORDER BY image_order ASC',
            ['chapter_id' => $chapterId]
        )->fetchAll(),
        'image_url'
    );

    return $chapter;
}

function delete_chapter(int $chapterId): void
{
    $mangaId = (int) db_query('SELECT manga_id FROM chapters WHERE id = :id', ['id' => $chapterId])->fetchColumn();
    db_query('DELETE FROM chapters WHERE id = :id', ['id' => $chapterId]);
    if ($mangaId > 0) {
        sync_manga_chapter_count($mangaId);
    }
}
