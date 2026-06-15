<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/http_error.php';
require_once __DIR__ . '/image.php';

function smartcms_board_key(string $value): string
{
    return preg_replace('/[^a-zA-Z0-9_-]/', '', trim($value));
}

function smartcms_board_url(string $board_key, string $path = '/board/'): string
{
    return smartcms_base_url($path) . '?board=' . rawurlencode($board_key);
}

function smartcms_board_skin_template(?array $board, string $template): string
{
    $skin = $board ? preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($board['skin'] ?? 'default')) : 'default';
    $path = SMARTCMS_ROOT . '/skins/board/' . ($skin !== '' ? $skin : 'default') . '/' . $template . '.php';
    if (is_file($path)) {
        return $path;
    }

    return SMARTCMS_ROOT . '/skins/board/default/' . $template . '.php';
}

function smartcms_board_skin_meta(?array $board): array
{
    $skin = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($board['skin'] ?? 'default')));
    $meta_map = [
        'default' => ['label' => '기본', 'accent' => 'primary', 'layout' => 'table', 'icon' => 'bi-layout-text-window-reverse'],
        'table' => ['label' => '테이블', 'accent' => 'primary', 'layout' => 'table', 'icon' => 'bi-table'],
        'card' => ['label' => '카드', 'accent' => 'primary', 'layout' => 'cards', 'icon' => 'bi-postcard-heart-fill'],
        'gallery' => ['label' => '갤러리', 'accent' => 'primary', 'layout' => 'cards', 'icon' => 'bi-grid-3x3-gap-fill'],
        'qna' => ['label' => 'Q&A', 'accent' => 'primary', 'layout' => 'table', 'icon' => 'bi-question-circle-fill'],
        'notice' => ['label' => '공지', 'accent' => 'primary', 'layout' => 'table', 'icon' => 'bi-megaphone-fill'],
        'faq' => ['label' => 'FAQ', 'accent' => 'primary', 'layout' => 'cards', 'icon' => 'bi-patch-question-fill'],
        'webzine' => ['label' => '웹진', 'accent' => 'primary', 'layout' => 'webzine', 'icon' => 'bi-journal-richtext'],
        'youtube' => ['label' => '유튜브', 'accent' => 'danger', 'layout' => 'webzine', 'icon' => 'bi-youtube'],
    ];

    $meta = $meta_map[$skin] ?? $meta_map['default'];
    $meta['skin'] = $skin;
    $meta['badge_class'] = 'bg-' . $meta['accent'] . '-subtle text-' . ($meta['accent'] === 'dark' ? 'dark' : $meta['accent']) . ' border border-' . $meta['accent'] . '-subtle';
    $meta['button_class'] = $meta['accent'] === 'dark' ? 'btn-dark' : 'btn-' . $meta['accent'];
    $meta['button_text_class'] = in_array($meta['accent'], ['warning', 'info'], true) ? 'text-dark' : 'text-white';
    $meta['header_class'] = 'border-top border-4 border-' . $meta['accent'];

    return $meta;
}

function smartcms_board_skin_options(): array
{
    return [
        'default' => '기본',
        'table' => '테이블',
        'card' => '카드',
        'gallery' => '갤러리',
        'qna' => 'Q&A',
        'notice' => '공지사항',
        'faq' => 'FAQ',
        'webzine' => '웹진',
        'youtube' => '유튜브',
    ];
}

function smartcms_board_normalize_skin(string $skin): string
{
    $skin = strtolower(trim(preg_replace('/[^a-zA-Z0-9_-]/', '', $skin)));
    return array_key_exists($skin, smartcms_board_skin_options()) ? $skin : 'default';
}

function smartcms_board_thumbnail_config(?array $board, string $context = 'list'): array
{
    $skin = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($board['skin'] ?? 'default')));
    $context = strtolower(trim($context));
    $context = in_array($context, ['list', 'view', 'widget'], true) ? $context : 'list';

    $map = [
        'default' => [
            'list' => [640, 360],
            'view' => [900, 506],
            'widget' => [480, 270],
            'columns' => 1,
        ],
        'table' => [
            'list' => [640, 360],
            'view' => [900, 506],
            'widget' => [480, 270],
            'columns' => 1,
        ],
        'card' => [
            'list' => [640, 360],
            'view' => [900, 506],
            'widget' => [480, 270],
            'columns' => 2,
        ],
        'gallery' => [
            'list' => [480, 480],
            'view' => [900, 506],
            'widget' => [480, 270],
            'columns' => 2,
        ],
        'qna' => [
            'list' => [640, 360],
            'view' => [900, 506],
            'widget' => [480, 270],
            'columns' => 1,
        ],
        'notice' => [
            'list' => [640, 360],
            'view' => [900, 506],
            'widget' => [480, 270],
            'columns' => 1,
        ],
        'faq' => [
            'list' => [640, 360],
            'view' => [900, 506],
            'widget' => [480, 270],
            'columns' => 2,
        ],
        'webzine' => [
            'list' => [640, 360],
            'view' => [900, 506],
            'widget' => [480, 270],
            'columns' => 1,
        ],
        'youtube' => [
            'list' => [640, 360],
            'view' => [960, 540],
            'widget' => [480, 270],
            'columns' => 1,
        ],
    ];

    $skin_config = $map[$skin] ?? $map['default'];
    $size = $skin_config[$context] ?? $skin_config['list'];

    return [
        'width' => (int)$size[0],
        'height' => (int)$size[1],
        'columns' => (int)($skin_config['columns'] ?? 1),
    ];
}

function smartcms_board_normalize_content_mode(string $value): string
{
    return strtolower(trim($value)) === 'editor' ? 'editor' : 'text';
}

function smartcms_board_author_display_options(): array
{
    return [
        'name' => '회원명',
        'nickname' => '닉네임',
        'name_nickname' => '회원명 + 닉네임',
    ];
}

function smartcms_board_normalize_author_display_mode(string $value): string
{
    $value = strtolower(trim($value));
    return array_key_exists($value, smartcms_board_author_display_options()) ? $value : 'name';
}

function smartcms_board_author_display_mode(?array $board): string
{
    return smartcms_board_normalize_author_display_mode((string)smartcms_setting('author_display_mode', 'name'));
}

function smartcms_ensure_boards_author_display_mode_column(): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    try {
        $exists = (int)smartcms_fetch_value(
            "SELECT COUNT(*)
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table_name
               AND COLUMN_NAME = 'author_display_mode'",
            ['table_name' => smartcms_table('boards')]
        );

        if ($exists === 0) {
            smartcms_execute(
                "ALTER TABLE " . smartcms_table('boards') . "
                 ADD COLUMN author_display_mode ENUM('name','nickname','name_nickname') NOT NULL DEFAULT 'name' AFTER display_type"
            );
        }
    } catch (Throwable $e) {
        // Keep the app usable even if schema migration is not allowed.
    }
}

function smartcms_board_user_profile_by_id(int $user_id): ?array
{
    static $cache = [];
    if ($user_id < 1) {
        return null;
    }

    if (array_key_exists($user_id, $cache)) {
        return $cache[$user_id];
    }

    smartcms_ensure_user_nickname_column();
    $cache[$user_id] = smartcms_fetch_one(
        "SELECT id, name, nickname
         FROM " . smartcms_table('users') . "
         WHERE id = :id
         LIMIT 1",
        ['id' => $user_id]
    ) ?: null;

    return $cache[$user_id];
}

function smartcms_board_author_display_name(?array $board, array $post): string
{
    $mode = smartcms_board_author_display_mode($board);
    if ($mode === 'name' && isset($post['author_display_mode'])) {
        $mode = smartcms_board_normalize_author_display_mode((string)$post['author_display_mode']);
    }

    $author_id = (int)($post['author_id'] ?? 0);
    $author_name = trim((string)($post['author_name'] ?? ''));
    $profile = $author_id > 0 ? smartcms_board_user_profile_by_id($author_id) : null;
    $name = trim((string)($profile['name'] ?? ''));
    $nickname = trim((string)($profile['nickname'] ?? ''));

    if ($mode === 'nickname') {
        return $nickname !== '' ? $nickname : ($name !== '' ? $name : $author_name);
    }

    if ($mode === 'name_nickname') {
        if ($name !== '' && $nickname !== '' && $name !== $nickname) {
            return $name . ' (' . $nickname . ')';
        }

        return $nickname !== '' ? $nickname : ($name !== '' ? $name : $author_name);
    }

    return $name !== '' ? $name : ($nickname !== '' ? $nickname : $author_name);
}

function smartcms_ensure_board_posts_content_mode_column(): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    try {
        $exists = (int)smartcms_fetch_value(
            "SELECT COUNT(*)
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table_name
               AND COLUMN_NAME = 'content_mode'",
            ['table_name' => smartcms_table('board_posts')]
        );

        if ($exists === 0) {
            smartcms_execute(
                "ALTER TABLE " . smartcms_table('board_posts') . "
                 ADD COLUMN content_mode ENUM('text','editor') NOT NULL DEFAULT 'text' AFTER content"
            );
        }
    } catch (Throwable $e) {
        // Keep the app usable even if schema migration is not allowed.
    }
}

function smartcms_board_normalize_link_url(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    if (preg_match('#^(https?:|mailto:|tel:|/|\\#)#i', $value) !== 1) {
        return '';
    }

    return $value;
}

function smartcms_board_youtube_video_id_from_url(string $url): ?string
{
    $url = trim($url);
    if ($url === '') {
        return null;
    }

    $video_id = null;
    $parts = parse_url($url);
    $host = strtolower((string)($parts['host'] ?? ''));
    $path = trim((string)($parts['path'] ?? ''), '/');
    $query = (string)($parts['query'] ?? '');

    if ($query !== '') {
        parse_str($query, $params);
        $candidate = (string)($params['v'] ?? '');
        if ($candidate !== '' && preg_match('/^[A-Za-z0-9_-]{11}$/', $candidate) === 1) {
            return $candidate;
        }
    }

    $segments = array_values(array_filter(explode('/', $path), static fn(string $segment): bool => $segment !== ''));
    if (str_contains($host, 'youtu.be') && !empty($segments)) {
        $candidate = $segments[0];
        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $candidate) === 1) {
            return $candidate;
        }
    }

    foreach (['embed', 'shorts', 'live', 'v'] as $marker) {
        $index = array_search($marker, $segments, true);
        if ($index !== false && isset($segments[$index + 1]) && preg_match('/^[A-Za-z0-9_-]{11}$/', $segments[$index + 1]) === 1) {
            return $segments[$index + 1];
        }
    }

    foreach ($segments as $segment) {
        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $segment) === 1) {
            return $segment;
        }
    }

    return $video_id;
}

function smartcms_board_youtube_thumbnail_cache_path(string $video_id, string $quality = 'hqdefault'): string
{
    $video_id = preg_replace('/[^A-Za-z0-9_-]/', '', $video_id);
    $quality = preg_replace('/[^A-Za-z0-9_.-]/', '', $quality);
    if ($quality === '') {
        $quality = 'hqdefault';
    }

    $dir = SMARTCMS_ROOT . '/uploads/board/youtube-thumbs/' . $quality;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    return $dir . '/' . $video_id . '.jpg';
}

function smartcms_board_download_remote_file(string $url, string $target_path): bool
{
    $dir = dirname($target_path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $data = false;
    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        if ($curl !== false) {
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_USERAGENT => 'SmartCMS/1.0',
            ]);
            $data = curl_exec($curl);
            curl_close($curl);
        }
    }

    if ($data === false || $data === '') {
        $context = stream_context_create([
            'http' => [
                'timeout' => 15,
                'header' => "User-Agent: SmartCMS/1.0\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);
        $data = @file_get_contents($url, false, $context);
    }

    if ($data === false || $data === '') {
        return false;
    }

    return file_put_contents($target_path, $data, LOCK_EX) !== false;
}

function smartcms_board_youtube_thumbnail_url_from_id(string $video_id, string $quality = 'hqdefault'): string
{
    $quality = preg_replace('/[^a-z0-9_.-]/i', '', $quality);
    if ($quality === '') {
        $quality = 'hqdefault';
    }

    $cache_path = smartcms_board_youtube_thumbnail_cache_path($video_id, $quality);
    if (!is_file($cache_path) || filesize($cache_path) === 0) {
        $remote_url = 'https://img.youtube.com/vi/' . rawurlencode($video_id) . '/' . rawurlencode($quality) . '.jpg';
        if (!smartcms_board_download_remote_file($remote_url, $cache_path)) {
            return $remote_url;
        }
    }

    $relative = ltrim(str_replace('\\', '/', str_replace(SMARTCMS_ROOT, '', $cache_path)), '/');
    return smartcms_asset_url('/' . $relative);
}

function smartcms_board_youtube_embed_url_from_id(string $video_id): string
{
    return 'https://www.youtube.com/embed/' . rawurlencode($video_id) . '?rel=0';
}

function smartcms_board_youtube_link_data(array $post): array
{
    $links = smartcms_board_post_links($post);
    foreach ($links as $url) {
        $video_id = smartcms_board_youtube_video_id_from_url($url);
        if ($video_id !== null) {
            return [
                'url' => $url,
                'video_id' => $video_id,
                'thumb_url' => smartcms_board_youtube_thumbnail_url_from_id($video_id),
                'embed_url' => smartcms_board_youtube_embed_url_from_id($video_id),
            ];
        }
    }

    return [
        'url' => '',
        'video_id' => null,
        'thumb_url' => null,
        'embed_url' => null,
    ];
}

function smartcms_board_post_links(array $post): array
{
    $links = [];
    foreach (['link_url_1', 'link_url_2', 'link_url'] as $key) {
        $url = smartcms_board_normalize_link_url((string)($post[$key] ?? ''));
        if ($url !== '' && !in_array($url, $links, true)) {
            $links[] = $url;
        }
    }

    return $links;
}

function smartcms_ensure_board_posts_link_column(): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    try {
        $table_name = smartcms_table('board_posts');
        $columns = ['link_url', 'link_url_1', 'link_url_2'];
        $existing = [];
        foreach ($columns as $column) {
            $existing[$column] = (int)smartcms_fetch_value(
                "SELECT COUNT(*)
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = :table_name
                   AND COLUMN_NAME = :column_name",
                [
                    'table_name' => $table_name,
                    'column_name' => $column,
                ]
            );
        }

        if ($existing['link_url'] === 0) {
            smartcms_execute(
                "ALTER TABLE " . $table_name . "
                 ADD COLUMN link_url VARCHAR(500) DEFAULT NULL AFTER title"
            );
        }
        if ($existing['link_url_1'] === 0) {
            smartcms_execute(
                "ALTER TABLE " . $table_name . "
                 ADD COLUMN link_url_1 VARCHAR(500) DEFAULT NULL AFTER link_url"
            );
        }
        if ($existing['link_url_2'] === 0) {
            smartcms_execute(
                "ALTER TABLE " . $table_name . "
                 ADD COLUMN link_url_2 VARCHAR(500) DEFAULT NULL AFTER link_url_1"
            );
        }

        if ($existing['link_url'] === 1 && $existing['link_url_1'] === 1) {
            smartcms_execute(
                "UPDATE " . $table_name . "
                 SET link_url_1 = COALESCE(NULLIF(link_url_1, ''), link_url)
                 WHERE (link_url_1 IS NULL OR link_url_1 = '')
                   AND link_url IS NOT NULL AND link_url <> ''"
            );
        }
    } catch (Throwable $e) {
        // Keep the app usable even if schema migration is not allowed.
    }
}

function smartcms_board_sanitize_editor_html(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    if (!class_exists(DOMDocument::class)) {
        return nl2br(smartcms_h(strip_tags($html)));
    }

    $previous = libxml_use_internal_errors(true);
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->loadHTML('<?xml encoding="utf-8"?><div id="smartcms-editor-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    $root = $dom->getElementById('smartcms-editor-root');
    if (!$root) {
        return smartcms_h($html);
    }

    $allowed = ['p', 'div', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li', 'blockquote', 'pre', 'code', 'a', 'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th', 'hr', 'span', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'img'];
    $sanitize_node = static function (DOMNode $node) use (&$sanitize_node, $allowed): string {
        if ($node->nodeType === XML_TEXT_NODE) {
            return htmlspecialchars($node->nodeValue ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return '';
        }

        $tag = strtolower($node->nodeName);
        $children = '';
        foreach ($node->childNodes as $child) {
            $children .= $sanitize_node($child);
        }

        if (!in_array($tag, $allowed, true)) {
            return $children;
        }

        return match ($tag) {
            'br' => '<br>',
            'p', 'div', 'strong', 'b', 'em', 'i', 'u', 'blockquote', 'pre', 'code', 'ul', 'ol', 'li' => '<' . $tag . '>' . $children . '</' . $tag . '>',
            'hr' => '<hr>',
            'span', 'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' => '<' . $tag . '>' . $children . '</' . $tag . '>',
            'img' => (function () use ($node): string {
                $src = '';
                if ($node->hasAttribute('src')) {
                    $candidate = trim((string)$node->getAttribute('src'));
                    if ($candidate !== '' && preg_match('#^(https?:|/|data:image/)#i', $candidate) === 1) {
                        $src = htmlspecialchars($candidate, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    }
                }

                if ($src === '') {
                    return '';
                }

                $alt = '';
                if ($node->hasAttribute('alt')) {
                    $alt = htmlspecialchars((string)$node->getAttribute('alt'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                }

                return '<img src="' . $src . '" alt="' . $alt . '" class="img-fluid rounded-3 d-block my-3" loading="lazy" decoding="async">';
            })(),
            'a' => (function () use ($node, $children): string {
                $href = '';
                if ($node->hasAttribute('href')) {
                    $candidate = trim((string)$node->getAttribute('href'));
                    if ($candidate !== '' && preg_match('#^(https?:|mailto:|/|\\#)#i', $candidate) === 1) {
                        $href = htmlspecialchars($candidate, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    }
                }

                if ($href === '') {
                    return $children;
                }

                return '<a href="' . $href . '" rel="nofollow noopener noreferrer">' . $children . '</a>';
            })(),
            default => $children,
        };
    };

    $output = '';
    foreach ($root->childNodes as $child) {
        $output .= $sanitize_node($child);
    }

    return $output;
}

function smartcms_board_render_content(array $post): string
{
    $content = (string)($post['content'] ?? '');
    if (smartcms_board_normalize_content_mode((string)($post['content_mode'] ?? 'text')) === 'editor') {
        return smartcms_board_sanitize_editor_html($content);
    }

    return nl2br(smartcms_h($content));
}

function smartcms_board_file_is_image(array $file): bool
{
    $mime = strtolower(trim((string)($file['mime_type'] ?? '')));
    if ($mime !== '' && str_starts_with($mime, 'image/')) {
        return true;
    }

    $path = strtolower(trim((string)($file['file_path'] ?? '')));
    if ($path === '') {
        $path = strtolower(trim((string)($file['original_name'] ?? '')));
    }

    return (bool)preg_match('/\.(png|jpe?g|gif|webp|bmp|avif)$/i', $path);
}

function smartcms_board_image_files(array $files): array
{
    return array_values(array_filter($files, static fn(array $file): bool => smartcms_board_file_is_image($file)));
}

function smartcms_board_editor_upload_dir(array $board): string
{
    $board_key = smartcms_board_key((string)($board['board_key'] ?? 'editor'));
    if ($board_key === '') {
        $board_key = 'editor';
    }

    return SMARTCMS_ROOT . '/uploads/board/editor/' . $board_key;
}

function smartcms_board_editor_upload_url(array $board, string $stored_name): string
{
    $board_key = smartcms_board_key((string)($board['board_key'] ?? 'editor'));
    if ($board_key === '') {
        $board_key = 'editor';
    }

    return smartcms_base_url('/uploads/board/editor/' . rawurlencode($board_key) . '/' . rawurlencode($stored_name));
}

function smartcms_board_editor_image_paths(string $html): array
{
    $paths = [];
    foreach (smartcms_image_extract_sources_from_html($html) as $source) {
        $real = smartcms_image_source_path_from_url($source);
        if ($real !== null) {
            $paths[] = $real;
        }
    }

    return array_values(array_unique($paths));
}

function smartcms_board_delete_editor_images_removed(string $old_content, string $new_content): void
{
    $old_paths = smartcms_board_editor_image_paths($old_content);
    if (!$old_paths) {
        return;
    }

    $new_paths = smartcms_board_editor_image_paths($new_content);
    foreach (array_diff($old_paths, $new_paths) as $path) {
        if (is_file($path)) {
            smartcms_image_delete_thumbnail_cache_for_source($path);
            @unlink($path);
        }
    }
}

function smartcms_board_delete_editor_images(string $content): void
{
    smartcms_board_delete_editor_images_removed($content, '');
}

function smartcms_board_editor_images_from_content(string $content): array
{
    $items = [];
    foreach (smartcms_image_extract_sources_from_html($content) as $source) {
        $path = smartcms_image_source_path_from_url($source);
        if ($path === null) {
            continue;
        }

        $relative = ltrim(str_replace('\\', '/', str_replace(SMARTCMS_ROOT, '', $path)), '/');
        $thumb_url = smartcms_image_thumbnail_url_from_path($path, 900, 506);
        $items[] = [
            'src' => $source,
            'path' => $path,
            'thumb_url' => $thumb_url ?? smartcms_asset_url('/' . $relative),
            'original_name' => basename((string)(parse_url($source, PHP_URL_PATH) ?: $source)),
            'file_size' => filesize($path) ?: 0,
        ];
    }

    return $items;
}

function smartcms_board_first_image_file(int $post_id): ?array
{
    return smartcms_fetch_one(
        "SELECT id, original_name, stored_name, file_path, file_size, mime_type, download_count, created_at
         FROM " . smartcms_table('board_files') . "
         WHERE post_id = :post_id AND mime_type LIKE 'image/%'
         ORDER BY id ASC
         LIMIT 1",
        ['post_id' => $post_id]
    );
}

function smartcms_board_file_thumbnail_url(array $file, int $max_width = 480, int $max_height = 270, int $quality = 85): ?string
{
    $relative_path = trim((string)($file['file_path'] ?? ''));
    if ($relative_path === '') {
        return null;
    }

    return smartcms_image_thumbnail_url_from_relative($relative_path, $max_width, $max_height, $quality);
}

function smartcms_board_truncate_title(string $title): string
{
    return trim(strip_tags($title));
}

function smartcms_board_highlight_text(string $text, string $keyword): string
{
    $text = smartcms_h($text);
    $keyword = trim($keyword);
    if ($keyword === '') {
        return $text;
    }

    $terms = preg_split('/\s+/u', $keyword) ?: [];
    $terms = array_values(array_unique(array_filter(array_map('trim', $terms), static fn(string $term): bool => $term !== '')));
    if (!$terms) {
        return $text;
    }

    usort($terms, static fn(string $a, string $b): int => strlen($b) <=> strlen($a));

    foreach ($terms as $term) {
        $escaped_term = smartcms_h($term);
        if ($escaped_term === '') {
            continue;
        }

        $pattern = '/' . preg_quote($escaped_term, '/') . '/iu';
        $text = preg_replace($pattern, '<mark class="bg-warning-subtle text-dark px-1 rounded">' . $escaped_term . '</mark>', $text) ?? $text;
    }

    return $text;
}

function smartcms_board_post_url(string $board_key, int $post_id): string
{
    return smartcms_board_url($board_key, '/board/view/') . '&id=' . rawurlencode((string)$post_id);
}

function smartcms_board_excerpt(string $content, int $length = 200): string
{
    $plain = strip_tags($content);
    if (function_exists('mb_substr')) {
        return mb_substr($plain, 0, $length);
    }

    return substr($plain, 0, $length);
}

function smartcms_home_date(?string $value): string
{
    if (!$value) {
        return '';
    }

    $ts = strtotime($value);
    return $ts ? date('m.d', $ts) : $value;
}

function smartcms_board_list(bool $include_disabled = false): array
{
    smartcms_ensure_boards_author_display_mode_column();
    $sql = "SELECT b.*, p.board_list_level, p.board_view_level, p.board_write_level, p.allow_guest_list, p.allow_guest_view
         FROM " . smartcms_table('boards') . " b
         LEFT JOIN " . smartcms_table('board_permissions') . " p ON p.board_key = b.board_key";
    if (!$include_disabled) {
        $sql .= " WHERE b.status <> 'disabled'";
    }
    $sql .= " ORDER BY b.id DESC";

    $stmt = smartcms_db()->query($sql);

    return $stmt->fetchAll();
}

function smartcms_board_find(string $board_key): ?array
{
    smartcms_ensure_boards_author_display_mode_column();
    return smartcms_fetch_one(
        "SELECT b.*, p.board_list_level, p.board_view_level, p.board_write_level, p.board_comment_level,
                p.board_upload_level, p.board_manage_level, p.allow_guest_list, p.allow_guest_view, p.status AS permission_status
         FROM " . smartcms_table('boards') . " b
         LEFT JOIN " . smartcms_table('board_permissions') . " p ON p.board_key = b.board_key
         WHERE b.board_key = :board_key
         LIMIT 1",
        ['board_key' => $board_key]
    );
}

function smartcms_require_board_access(array $board, string $action): ?array
{
    $level_key = [
        'list' => 'board_list_level',
        'view' => 'board_view_level',
        'write' => 'board_write_level',
    ][$action] ?? 'board_view_level';
    $guest_key = $action === 'list' ? 'allow_guest_list' : 'allow_guest_view';
    $required_level = (int)($board[$level_key] ?? 0);
    $user = smartcms_current_user();
    $user_id = $user ? (int)$user['id'] : null;

    if ((string)($board['status'] ?? 'active') === 'disabled' || (string)($board['permission_status'] ?? 'active') === 'disabled') {
        smartcms_log_access('permission_denied', 'board', (string)$board['board_key'], 'denied', 403, $user_id);
        smartcms_render_access_denied_page('현재 비활성화된 게시판입니다.');
    }

    if ($action === 'write' && !$user) {
        smartcms_redirect((string)smartcms_config_value('login_url', '/member/login/'));
    }

    if ($required_level <= 0 || ((int)($board[$guest_key] ?? 0) === 1 && !$user && $action !== 'write')) {
        smartcms_log_access('page_view', 'board', (string)$board['board_key'], 'success', 200, $user_id);
        return $user;
    }

    if (!$user) {
        smartcms_redirect((string)smartcms_config_value('login_url', '/member/login/'));
    }

    if (!smartcms_has_level($required_level, $user)) {
        smartcms_log_access('permission_denied', 'board', (string)$board['board_key'], 'denied', 403, (int)$user['id']);
        smartcms_render_access_denied_page('이 게시판을 볼 권한이 없습니다.');
    }

    smartcms_log_access('page_view', 'board', (string)$board['board_key'], 'success', 200, (int)$user['id']);
    return $user;
}

function smartcms_board_search_term(string $keyword): string
{
    return trim(str_replace(['%', '_'], ['\\%', '\\_'], $keyword));
}

function smartcms_board_posts(int $board_id, int $page = 1, int $per_page = 10, string $keyword = ''): array
{
    $page = max(1, $page);
    $per_page = max(1, min(100, $per_page));
    $offset = ($page - 1) * $per_page;
    $keyword = smartcms_board_search_term($keyword);
    $where = "p.board_id = :board_id AND p.is_hidden = 0";
    $params = ['board_id' => $board_id];

    if ($keyword !== '') {
        $where .= " AND (
            p.title LIKE :keyword_title
            OR p.content LIKE :keyword_content
            OR p.author_name LIKE :keyword_author_name
            OR u.nickname LIKE :keyword_nickname
        )";
        $params['keyword_title'] = '%' . $keyword . '%';
        $params['keyword_content'] = '%' . $keyword . '%';
        $params['keyword_author_name'] = '%' . $keyword . '%';
        $params['keyword_nickname'] = '%' . $keyword . '%';
    }

    $count_stmt = smartcms_db()->prepare(
        "SELECT COUNT(*) AS cnt
         FROM " . smartcms_table('board_posts') . " p
         INNER JOIN " . smartcms_table('boards') . " b ON b.id = p.board_id
         LEFT JOIN " . smartcms_table('users') . " u ON u.id = p.author_id
         WHERE {$where}"
    );
    $count_stmt->execute($params);
    $total = (int)($count_stmt->fetch()['cnt'] ?? 0);

    $stmt = smartcms_db()->prepare(
        "SELECT p.id, p.title, p.link_url, p.link_url_1, p.link_url_2, p.excerpt, p.author_id, p.author_name, p.is_notice, p.is_secret, p.view_count, p.comment_count, p.attachment_count, p.created_at,
                b.author_display_mode
         FROM " . smartcms_table('board_posts') . " p
         INNER JOIN " . smartcms_table('boards') . " b ON b.id = p.board_id
         LEFT JOIN " . smartcms_table('users') . " u ON u.id = p.author_id
         WHERE {$where}
         ORDER BY p.is_notice DESC, p.id DESC
         LIMIT :limit OFFSET :offset"
    );
    $stmt->bindValue('board_id', $board_id, PDO::PARAM_INT);
    if ($keyword !== '') {
        $like = '%' . $keyword . '%';
        $stmt->bindValue('keyword_title', $like, PDO::PARAM_STR);
        $stmt->bindValue('keyword_content', $like, PDO::PARAM_STR);
        $stmt->bindValue('keyword_author_name', $like, PDO::PARAM_STR);
        $stmt->bindValue('keyword_nickname', $like, PDO::PARAM_STR);
    }
    $stmt->bindValue('limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return [
        'items' => $stmt->fetchAll(),
        'total' => $total,
        'page' => $page,
        'per_page' => $per_page,
        'pages' => max(1, (int)ceil($total / $per_page)),
        'keyword' => $keyword,
    ];
}

function smartcms_board_search_posts(string $keyword, int $page = 1, int $per_page = 12): array
{
    $page = max(1, $page);
    $per_page = max(1, min(100, $per_page));
    $offset = ($page - 1) * $per_page;
    $keyword = smartcms_board_search_term($keyword);
    $where = "p.is_hidden = 0 AND b.status <> 'disabled'";
    $params = [];

    if ($keyword !== '') {
        $where .= " AND (
            p.title LIKE :keyword_title
            OR p.content LIKE :keyword_content
            OR b.board_name LIKE :keyword_board
            OR p.author_name LIKE :keyword_author_name
            OR u.nickname LIKE :keyword_nickname
        )";
        $like = '%' . $keyword . '%';
        $params['keyword_title'] = $like;
        $params['keyword_content'] = $like;
        $params['keyword_board'] = $like;
        $params['keyword_author_name'] = $like;
        $params['keyword_nickname'] = $like;
    }

    $count_stmt = smartcms_db()->prepare(
        "SELECT COUNT(*) AS cnt
         FROM " . smartcms_table('board_posts') . " p
         INNER JOIN " . smartcms_table('boards') . " b ON b.id = p.board_id
         LEFT JOIN " . smartcms_table('users') . " u ON u.id = p.author_id
         WHERE {$where}"
    );
    $count_stmt->execute($params);
    $total = (int)($count_stmt->fetch()['cnt'] ?? 0);

    $stmt = smartcms_db()->prepare(
        "SELECT p.id, p.title, p.link_url, p.link_url_1, p.link_url_2, p.content, p.excerpt, p.author_id, p.author_name, p.is_notice, p.is_secret, p.view_count, p.comment_count, p.attachment_count, p.created_at,
                b.board_key, b.board_name, b.author_display_mode
         FROM " . smartcms_table('board_posts') . " p
         INNER JOIN " . smartcms_table('boards') . " b ON b.id = p.board_id
         LEFT JOIN " . smartcms_table('users') . " u ON u.id = p.author_id
         WHERE {$where}
         ORDER BY p.is_notice DESC, p.id DESC
         LIMIT :limit OFFSET :offset"
    );
    if ($keyword !== '') {
        $stmt->bindValue('keyword_title', '%' . $keyword . '%', PDO::PARAM_STR);
        $stmt->bindValue('keyword_content', '%' . $keyword . '%', PDO::PARAM_STR);
        $stmt->bindValue('keyword_board', '%' . $keyword . '%', PDO::PARAM_STR);
        $stmt->bindValue('keyword_author_name', '%' . $keyword . '%', PDO::PARAM_STR);
        $stmt->bindValue('keyword_nickname', '%' . $keyword . '%', PDO::PARAM_STR);
    }
    $stmt->bindValue('limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return [
        'items' => $stmt->fetchAll(),
        'total' => $total,
        'page' => $page,
        'per_page' => $per_page,
        'pages' => max(1, (int)ceil($total / $per_page)),
        'keyword' => $keyword,
    ];
}

function smartcms_board_post_find(int $board_id, int $post_id): ?array
{
    smartcms_ensure_board_posts_content_mode_column();
    smartcms_ensure_board_posts_link_column();
    return smartcms_fetch_one(
        "SELECT p.id, p.board_id, p.title, p.link_url, p.link_url_1, p.link_url_2, p.content, p.content_mode, p.author_id, p.author_name, p.is_notice, p.is_secret, p.view_count, p.comment_count, p.created_at, p.updated_at,
                b.author_display_mode
         FROM " . smartcms_table('board_posts') . " p
         INNER JOIN " . smartcms_table('boards') . " b ON b.id = p.board_id
         WHERE p.board_id = :board_id AND p.id = :id AND p.is_hidden = 0
         LIMIT 1",
        [
            'board_id' => $board_id,
            'id' => $post_id,
        ]
    );
}

function smartcms_board_increment_view(int $post_id): void
{
    smartcms_execute(
        "UPDATE " . smartcms_table('board_posts') . " SET view_count = view_count + 1 WHERE id = :id",
        ['id' => $post_id]
    );
}

function smartcms_board_comments(int $post_id): array
{
    $stmt = smartcms_db()->prepare(
        "SELECT id, author_id, author_name, content, is_hidden, created_at, updated_at
         FROM " . smartcms_table('board_comments') . "
         WHERE post_id = :post_id
         ORDER BY id ASC"
    );
    $stmt->execute(['post_id' => $post_id]);

    return $stmt->fetchAll();
}

function smartcms_board_files(int $post_id): array
{
    $stmt = smartcms_db()->prepare(
        "SELECT id, original_name, stored_name, file_path, file_size, mime_type, download_count, created_at
         FROM " . smartcms_table('board_files') . "
         WHERE post_id = :post_id
         ORDER BY id ASC"
    );
    $stmt->execute(['post_id' => $post_id]);

    return $stmt->fetchAll();
}

function smartcms_board_file_find(int $file_id): ?array
{
    return smartcms_fetch_one(
        "SELECT f.*, p.title, p.author_id, p.is_secret, p.is_hidden, b.board_key, b.board_name, b.status AS board_status,
                bp.board_view_level, bp.board_manage_level, bp.allow_guest_view, bp.status AS permission_status
         FROM " . smartcms_table('board_files') . " f
         INNER JOIN " . smartcms_table('board_posts') . " p ON p.id = f.post_id
         INNER JOIN " . smartcms_table('boards') . " b ON b.id = f.board_id
         LEFT JOIN " . smartcms_table('board_permissions') . " bp ON bp.board_key = b.board_key
         WHERE f.id = :id
         LIMIT 1",
        ['id' => $file_id]
    );
}

function smartcms_board_can_manage_post(array $board, array $post, ?array $user): bool
{
    if (!$user) {
        return false;
    }

    return (int)$post['author_id'] === (int)$user['id'] || smartcms_has_level((int)($board['board_manage_level'] ?? 8), $user);
}

function smartcms_board_should_count_view(array $board, array $post, ?array $user): bool
{
    if (!$user) {
        return true;
    }

    if ((int)($post['author_id'] ?? 0) === (int)$user['id']) {
        return false;
    }

    return !smartcms_has_level((int)($board['board_manage_level'] ?? 8), $user);
}

function smartcms_board_should_count_download(array $board, array $file, ?array $user): bool
{
    if (!$user) {
        return true;
    }

    if ((int)($file['author_id'] ?? 0) === (int)$user['id']) {
        return false;
    }

    return !smartcms_has_level((int)($board['board_manage_level'] ?? 8), $user);
}

function smartcms_board_count_once(string $type, int $id, int $ttl = 86400): bool
{
    smartcms_session_start();
    $_SESSION['smartcms_board_count_cache'] ??= [];

    $key = $type . ':' . $id;
    if ($ttl <= 0) {
        if (!empty($_SESSION['smartcms_board_count_cache'][$key])) {
            return false;
        }

        $_SESSION['smartcms_board_count_cache'][$key] = true;
        return true;
    }

    $now = time();
    $last = (int)($_SESSION['smartcms_board_count_cache'][$key] ?? 0);
    if ($last > 0 && ($now - $last) < $ttl) {
        return false;
    }

    $_SESSION['smartcms_board_count_cache'][$key] = $now;
    return true;
}

function smartcms_board_create_comment(array $board, array $post, array $user, string $content): array
{
    $content = trim($content);
    if ($content === '') {
        return ['ok' => false, 'message' => '댓글 내용을 입력하세요.'];
    }

    smartcms_execute(
        "INSERT INTO " . smartcms_table('board_comments') . "
         (board_id, post_id, author_id, author_name, content)
         VALUES (:board_id, :post_id, :author_id, :author_name, :content)",
        [
            'board_id' => (int)$board['id'],
            'post_id' => (int)$post['id'],
            'author_id' => (int)$user['id'],
            'author_name' => (string)$user['name'],
            'content' => $content,
        ]
    );

    smartcms_execute(
        "UPDATE " . smartcms_table('board_posts') . " SET comment_count = comment_count + 1 WHERE id = :id",
        ['id' => (int)$post['id']]
    );

    smartcms_board_audit($board, $post, $user, 'comment_create', '댓글을 등록했습니다.');
    return ['ok' => true, 'message' => '댓글을 등록했습니다.'];
}

function smartcms_board_update_post(array $board, array $post, array $user, string $title, string $link_url_1, string $link_url_2, string $content, string $content_mode, bool $is_notice, bool $is_secret): array
{
    smartcms_ensure_board_posts_content_mode_column();
    smartcms_ensure_board_posts_link_column();
    if (!smartcms_board_can_manage_post($board, $post, $user)) {
        return ['ok' => false, 'message' => '글 수정 권한이 없습니다.'];
    }

    $title = trim($title);
    $link_url_1 = smartcms_board_normalize_link_url($link_url_1);
    $link_url_2 = smartcms_board_normalize_link_url($link_url_2);
    $content = trim($content);
    $content_mode = smartcms_board_normalize_content_mode($content_mode);
    if ($title === '' || $content === '') {
        return ['ok' => false, 'message' => '제목과 내용을 입력하세요.'];
    }

    $can_notice = smartcms_has_level((int)($board['board_manage_level'] ?? 8), $user);
    smartcms_execute(
        "UPDATE " . smartcms_table('board_posts') . "
         SET title = :title,
             link_url = :link_url,
             link_url_1 = :link_url_1,
             link_url_2 = :link_url_2,
             content = :content,
             content_mode = :content_mode,
             excerpt = :excerpt,
             is_notice = :is_notice,
             is_secret = :is_secret
         WHERE id = :id AND board_id = :board_id",
        [
            'id' => (int)$post['id'],
            'board_id' => (int)$board['id'],
            'title' => $title,
            'link_url' => $link_url_1 !== '' ? $link_url_1 : null,
            'link_url_1' => $link_url_1 !== '' ? $link_url_1 : null,
            'link_url_2' => $link_url_2 !== '' ? $link_url_2 : null,
            'content' => $content,
            'content_mode' => $content_mode,
            'excerpt' => smartcms_board_excerpt($content),
            'is_notice' => $can_notice && $is_notice ? 1 : 0,
            'is_secret' => $is_secret ? 1 : 0,
        ]
    );

    smartcms_board_delete_editor_images_removed((string)($post['content'] ?? ''), $content);

    smartcms_board_audit($board, $post, $user, 'post_update', '게시글을 수정했습니다.');
    return ['ok' => true, 'message' => '글을 수정했습니다.'];
}

function smartcms_board_hide_post(array $board, array $post, array $user): array
{
    if (!smartcms_board_can_manage_post($board, $post, $user)) {
        return ['ok' => false, 'message' => '글 숨김 권한이 없습니다.'];
    }

    smartcms_execute(
        "UPDATE " . smartcms_table('board_posts') . " SET is_hidden = 1 WHERE id = :id AND board_id = :board_id",
        [
            'id' => (int)$post['id'],
            'board_id' => (int)$board['id'],
        ]
    );

    smartcms_board_audit($board, $post, $user, 'post_hide', '게시글을 숨김 처리했습니다.');
    return ['ok' => true, 'message' => '글을 숨김 처리했습니다.'];
}

function smartcms_board_delete_post(array $board, array $post, array $user): array
{
    if (!smartcms_board_can_manage_post($board, $post, $user)) {
        return ['ok' => false, 'message' => '글 삭제 권한이 없습니다.'];
    }

    $files = smartcms_board_files((int)$post['id']);
    $db = smartcms_db();
    $db->beginTransaction();

    try {
        smartcms_execute(
            "DELETE FROM " . smartcms_table('board_comments') . " WHERE post_id = :post_id AND board_id = :board_id",
            [
                'post_id' => (int)$post['id'],
                'board_id' => (int)$board['id'],
            ]
        );
        smartcms_execute(
            "DELETE FROM " . smartcms_table('board_files') . " WHERE post_id = :post_id AND board_id = :board_id",
            [
                'post_id' => (int)$post['id'],
                'board_id' => (int)$board['id'],
            ]
        );
        smartcms_execute(
            "DELETE FROM " . smartcms_table('board_posts') . " WHERE id = :id AND board_id = :board_id",
            [
                'id' => (int)$post['id'],
                'board_id' => (int)$board['id'],
            ]
        );

        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }

        return ['ok' => false, 'message' => '글 삭제 중 오류가 발생했습니다.'];
    }

    foreach ($files as $file) {
        $path = SMARTCMS_ROOT . '/' . ltrim((string)$file['file_path'], '/');
        smartcms_image_delete_thumbnail_cache_for_source($path);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    smartcms_board_delete_editor_images((string)($post['content'] ?? ''));
    smartcms_board_audit($board, $post, $user, 'post_delete', '게시글을 삭제했습니다.');
    return ['ok' => true, 'message' => '글을 삭제했습니다.'];
}

function smartcms_board_delete_post_core(array $board, array $post): array
{
    $files = smartcms_board_files((int)$post['id']);
    $db = smartcms_db();
    $db->beginTransaction();

    try {
        smartcms_execute(
            "DELETE FROM " . smartcms_table('board_comments') . " WHERE post_id = :post_id AND board_id = :board_id",
            [
                'post_id' => (int)$post['id'],
                'board_id' => (int)$board['id'],
            ]
        );
        smartcms_execute(
            "DELETE FROM " . smartcms_table('board_files') . " WHERE post_id = :post_id AND board_id = :board_id",
            [
                'post_id' => (int)$post['id'],
                'board_id' => (int)$board['id'],
            ]
        );
        smartcms_execute(
            "DELETE FROM " . smartcms_table('board_posts') . " WHERE id = :id AND board_id = :board_id",
            [
                'id' => (int)$post['id'],
                'board_id' => (int)$board['id'],
            ]
        );

        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }

        return ['ok' => false, 'message' => '글 삭제 중 오류가 발생했습니다.'];
    }

    foreach ($files as $file) {
        $path = SMARTCMS_ROOT . '/' . ltrim((string)$file['file_path'], '/');
        smartcms_image_delete_thumbnail_cache_for_source($path);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    smartcms_board_delete_editor_images((string)($post['content'] ?? ''));
    return ['ok' => true, 'message' => '글을 삭제했습니다.'];
}

function smartcms_board_move_post_to_board(array $source_board, array $post, array $target_board, array $user): array
{
    if (!smartcms_board_can_manage_post($source_board, $post, $user)) {
        return ['ok' => false, 'message' => '글 이동 권한이 없습니다.'];
    }

    if ((int)($source_board['id'] ?? 0) === (int)($target_board['id'] ?? 0)) {
        return ['ok' => false, 'message' => '이동할 대상 게시판이 현재 게시판과 같습니다.'];
    }

    if (!smartcms_has_level((int)($target_board['board_write_level'] ?? 8), $user)) {
        return ['ok' => false, 'message' => '대상 게시판에 글을 쓸 권한이 없습니다.'];
    }

    $db = smartcms_db();
    $db->beginTransaction();
    $content = (string)($post['content'] ?? '');
    $editor_result = smartcms_board_copy_editor_content_to_board($content, $source_board, $target_board, false);
    $content = (string)$editor_result['content'];

    try {
        smartcms_execute(
            "UPDATE " . smartcms_table('board_posts') . "
             SET board_id = :target_board_id, content = :content
             WHERE id = :id AND board_id = :source_board_id",
            [
                'target_board_id' => (int)$target_board['id'],
                'source_board_id' => (int)$source_board['id'],
                'id' => (int)$post['id'],
                'content' => $content,
            ]
        );

        smartcms_execute(
            "UPDATE " . smartcms_table('board_comments') . "
             SET board_id = :target_board_id
             WHERE post_id = :post_id AND board_id = :source_board_id",
            [
                'target_board_id' => (int)$target_board['id'],
                'source_board_id' => (int)$source_board['id'],
                'post_id' => (int)$post['id'],
            ]
        );

        smartcms_execute(
            "UPDATE " . smartcms_table('board_files') . "
             SET board_id = :target_board_id
             WHERE post_id = :post_id AND board_id = :source_board_id",
            [
                'target_board_id' => (int)$target_board['id'],
                'source_board_id' => (int)$source_board['id'],
                'post_id' => (int)$post['id'],
            ]
        );

        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }

        return ['ok' => false, 'message' => '글 이동 중 오류가 발생했습니다.'];
    }

    smartcms_board_audit($source_board, $post, $user, 'post_move', '게시글을 이동했습니다.');
    return ['ok' => true, 'message' => '선택한 글을 이동했습니다.'];
}

function smartcms_board_copy_post_to_board(array $source_board, array $post, array $target_board, array $user): array
{
    if (!smartcms_board_can_manage_post($source_board, $post, $user)) {
        return ['ok' => false, 'message' => '글 복사 권한이 없습니다.'];
    }

    if (!smartcms_has_level((int)($target_board['board_write_level'] ?? 8), $user)) {
        return ['ok' => false, 'message' => '대상 게시판에 글을 쓸 권한이 없습니다.'];
    }

    $db = smartcms_db();
    $db->beginTransaction();
    $source_files = smartcms_board_files((int)$post['id']);
    $content = (string)($post['content'] ?? '');
    $editor_result = smartcms_board_copy_editor_content_to_board($content, $source_board, $target_board, true);
    $content = (string)$editor_result['content'];

    try {
        smartcms_execute(
            "INSERT INTO " . smartcms_table('board_posts') . "
             (board_id, parent_id, category, title, link_url, link_url_1, link_url_2, content, content_mode, excerpt, author_id, author_name, is_notice, is_secret, is_hidden)
             VALUES (:board_id, NULL, NULL, :title, :link_url, :link_url_1, :link_url_2, :content, :content_mode, :excerpt, :author_id, :author_name, :is_notice, :is_secret, 0)",
            [
                'board_id' => (int)$target_board['id'],
                'title' => (string)$post['title'],
                'link_url' => $post['link_url'] !== null ? (string)$post['link_url'] : null,
                'link_url_1' => $post['link_url_1'] !== null ? (string)$post['link_url_1'] : null,
                'link_url_2' => $post['link_url_2'] !== null ? (string)$post['link_url_2'] : null,
                'content' => $content,
                'content_mode' => smartcms_board_normalize_content_mode((string)($post['content_mode'] ?? 'text')),
                'excerpt' => smartcms_board_excerpt($content),
                'author_id' => (int)($post['author_id'] ?? 0) ?: null,
                'author_name' => (string)($post['author_name'] ?? ''),
                'is_notice' => (int)($post['is_notice'] ?? 0) === 1 ? 1 : 0,
                'is_secret' => (int)($post['is_secret'] ?? 0) === 1 ? 1 : 0,
            ]
        );

        $target_post_id = (int)$db->lastInsertId();
        $copied_files = smartcms_board_copy_post_files($source_files, $target_board, $target_post_id, (int)$user['id']);
        if ($copied_files > 0) {
            smartcms_execute(
                "UPDATE " . smartcms_table('board_posts') . " SET attachment_count = :count WHERE id = :id",
                ['count' => $copied_files, 'id' => $target_post_id]
            );
        }

        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }

        return ['ok' => false, 'message' => '글 복사 중 오류가 발생했습니다.'];
    }

    smartcms_board_audit($source_board, $post, $user, 'post_copy', '게시글을 복사했습니다.');
    return ['ok' => true, 'message' => '선택한 글을 복사했습니다.'];
}

function smartcms_board_bulk_action_posts(array $source_board, array $user, array $post_ids, string $action, ?string $target_board_key = null): array
{
    $post_ids = smartcms_board_post_ids($post_ids);
    if (!$post_ids) {
        return ['ok' => false, 'message' => '선택한 글이 없습니다.'];
    }

    if (!smartcms_has_level((int)($source_board['board_manage_level'] ?? 8), $user)) {
        return ['ok' => false, 'message' => '선택한 글을 관리할 권한이 없습니다.'];
    }

    $posts = smartcms_board_posts_by_ids((int)$source_board['id'], $post_ids);
    if (count($posts) !== count($post_ids)) {
        return ['ok' => false, 'message' => '선택한 글 중 일부를 찾지 못했습니다.'];
    }

    $count = 0;
    $action = strtolower(trim($action));

    if ($action === 'delete') {
        foreach ($posts as $post) {
            $result = smartcms_board_delete_post_core($source_board, $post);
            if (!$result['ok']) {
                return $result;
            }
            $count++;
        }

        smartcms_board_audit($source_board, null, $user, 'post_bulk_delete', '선택한 글 ' . $count . '개를 삭제했습니다.');
        return ['ok' => true, 'message' => '선택한 글 ' . $count . '개를 삭제했습니다.'];
    }

    if ($target_board_key === null || trim($target_board_key) === '') {
        return ['ok' => false, 'message' => '대상 게시판을 선택하세요.'];
    }

    $target_board = smartcms_board_find($target_board_key);
    if (!$target_board || (string)($target_board['status'] ?? '') === 'disabled') {
        return ['ok' => false, 'message' => '대상 게시판을 찾을 수 없습니다.'];
    }

    foreach ($posts as $post) {
        $result = $action === 'move'
            ? smartcms_board_move_post_to_board($source_board, $post, $target_board, $user)
            : ($action === 'copy' ? smartcms_board_copy_post_to_board($source_board, $post, $target_board, $user) : ['ok' => false, 'message' => '지원하지 않는 작업입니다.']);
        if (!$result['ok']) {
            return $result;
        }
        $count++;
    }

    $audit_action = $action === 'move' ? 'post_bulk_move' : 'post_bulk_copy';
    smartcms_board_audit($source_board, null, $user, $audit_action, '선택한 글 ' . $count . '개를 ' . ($action === 'move' ? '이동' : '복사') . '했습니다.');
    return ['ok' => true, 'message' => '선택한 글 ' . $count . '개를 ' . ($action === 'move' ? '이동' : '복사') . '했습니다.'];
}

function smartcms_board_hide_comment(array $board, array $post, array $user, int $comment_id): array
{
    if (!smartcms_has_level((int)($board['board_manage_level'] ?? 8), $user)) {
        return ['ok' => false, 'message' => '댓글 숨김 권한이 없습니다.'];
    }

    smartcms_execute(
        "UPDATE " . smartcms_table('board_comments') . "
         SET is_hidden = 1
         WHERE id = :id AND post_id = :post_id AND board_id = :board_id",
        [
            'id' => $comment_id,
            'post_id' => (int)$post['id'],
            'board_id' => (int)$board['id'],
        ]
    );

    smartcms_board_audit($board, $post, $user, 'comment_hide', '댓글을 숨김 처리했습니다.');
    return ['ok' => true, 'message' => '댓글을 숨김 처리했습니다.'];
}

function smartcms_board_audit(array $board, ?array $post, ?array $user, string $action, string $message): void
{
    try {
        smartcms_execute(
            "INSERT INTO " . smartcms_table('board_audit_logs') . "
             (board_id, post_id, user_id, action, message, ip_hash, user_agent)
             VALUES (:board_id, :post_id, :user_id, :action, :message, :ip_hash, :user_agent)",
            [
                'board_id' => (int)$board['id'],
                'post_id' => $post ? (int)$post['id'] : null,
                'user_id' => $user ? (int)$user['id'] : null,
                'action' => $action,
                'message' => $message,
                'ip_hash' => smartcms_ip_hash(),
                'user_agent' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            ]
        );
    } catch (Throwable) {
        // Audit logging should not break content operations.
    }
}

function smartcms_board_create(string $board_key, string $board_name, string $description, int $created_by, string $skin = 'default'): array
{
    $board_key = smartcms_board_key($board_key);
    $board_name = trim($board_name);
    $skin = smartcms_board_normalize_skin($skin);

    if ($board_key === '' || $board_name === '') {
        return ['ok' => false, 'message' => '게시판 키와 이름을 입력하세요.'];
    }

    smartcms_ensure_boards_author_display_mode_column();
    $exists = smartcms_board_find($board_key);
    if ($exists) {
        return ['ok' => false, 'message' => '이미 존재하는 게시판 키입니다.'];
    }

    smartcms_execute(
        "INSERT INTO " . smartcms_table('boards') . "
         (board_key, board_name, description, skin, created_by)
         VALUES (:board_key, :board_name, :description, :skin, :created_by)",
        [
            'board_key' => $board_key,
            'board_name' => $board_name,
            'description' => trim($description) !== '' ? trim($description) : null,
            'skin' => $skin,
            'created_by' => $created_by,
        ]
    );

    smartcms_execute(
        "INSERT INTO " . smartcms_table('board_permissions') . "
         (board_key, board_name)
         VALUES (:board_key, :board_name)",
        [
            'board_key' => $board_key,
            'board_name' => $board_name,
        ]
    );

    return ['ok' => true, 'message' => '게시판을 생성했습니다.'];
}

function smartcms_board_seed_defaults(int $created_by): array
{
    $defaults = [
        ['free', '자유게시판', '자유롭게 이야기를 나누는 게시판입니다.'],
        ['notice', '공지사항', '사이트 공지와 안내를 게시합니다.'],
        ['qna', 'Q&A', '질문과 답변을 남기는 게시판입니다.'],
    ];
    $created = 0;

    foreach ($defaults as [$key, $name, $description]) {
        $result = smartcms_board_create($key, $name, $description, $created_by);
        if ($result['ok']) {
            $created++;
        }
    }

    return [
        'ok' => true,
        'message' => $created > 0 ? '기본 게시판 ' . $created . '개를 생성했습니다.' : '기본 게시판이 이미 준비되어 있습니다.',
        'created' => $created,
    ];
}

function smartcms_board_recent_posts(int $limit = 12): array
{
    $stmt = smartcms_db()->prepare(
        "SELECT p.id, p.title, p.link_url, p.link_url_1, p.link_url_2, p.excerpt, p.author_id, p.author_name, p.comment_count, p.attachment_count, p.created_at, b.board_key, b.board_name, b.author_display_mode
         FROM " . smartcms_table('board_posts') . " p
         INNER JOIN " . smartcms_table('boards') . " b ON b.id = p.board_id
         WHERE p.is_hidden = 0 AND b.status <> 'disabled'
         ORDER BY p.id DESC
         LIMIT :limit"
    );
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function smartcms_board_recent_posts_by_key(string $board_key, int $limit = 5): array
{
    $stmt = smartcms_db()->prepare(
        "SELECT p.id, p.title, p.link_url, p.link_url_1, p.link_url_2, p.excerpt, p.author_id, p.author_name, p.comment_count, p.attachment_count, p.view_count, p.created_at,
                b.board_key, b.board_name, b.author_display_mode
         FROM " . smartcms_table('board_posts') . " p
         INNER JOIN " . smartcms_table('boards') . " b ON b.id = p.board_id
         WHERE p.is_hidden = 0 AND b.status <> 'disabled' AND b.board_key = :board_key
         ORDER BY p.is_notice DESC, p.id DESC
         LIMIT :limit"
    );
    $stmt->bindValue('board_key', $board_key);
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function smartcms_board_popular_posts(int $limit = 5): array
{
    $stmt = smartcms_db()->prepare(
        "SELECT p.id, p.title, p.link_url, p.link_url_1, p.link_url_2, p.author_id, p.author_name, p.comment_count, p.view_count, p.created_at, b.board_key, b.board_name, b.author_display_mode
         FROM " . smartcms_table('board_posts') . " p
         INNER JOIN " . smartcms_table('boards') . " b ON b.id = p.board_id
         WHERE p.is_hidden = 0 AND b.status <> 'disabled'
         ORDER BY p.view_count DESC, p.comment_count DESC, p.id DESC
         LIMIT :limit"
    );
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function smartcms_board_bulk_target_options(array $source_board, array $user): array
{
    $targets = [];
    foreach (smartcms_board_list() as $candidate) {
        if ((int)($candidate['id'] ?? 0) === (int)($source_board['id'] ?? 0)) {
            continue;
        }

        if ((string)($candidate['status'] ?? '') === 'disabled') {
            continue;
        }

        if (!smartcms_has_level((int)($candidate['board_write_level'] ?? 8), $user)) {
            continue;
        }

        $targets[] = $candidate;
    }

    return $targets;
}

function smartcms_board_post_ids(array $post_ids): array
{
    $ids = [];
    foreach ($post_ids as $post_id) {
        $post_id = (int)$post_id;
        if ($post_id > 0) {
            $ids[$post_id] = $post_id;
        }
    }

    return array_values($ids);
}

function smartcms_board_posts_by_ids(int $board_id, array $post_ids): array
{
    $post_ids = smartcms_board_post_ids($post_ids);
    if (!$post_ids) {
        return [];
    }

    $placeholders = [];
    $params = ['board_id' => $board_id];
    foreach ($post_ids as $index => $post_id) {
        $key = 'post_id_' . $index;
        $placeholders[] = ':' . $key;
        $params[$key] = $post_id;
    }

    $stmt = smartcms_db()->prepare(
        "SELECT p.id, p.board_id, p.title, p.link_url, p.link_url_1, p.link_url_2, p.content, p.content_mode, p.excerpt, p.author_id, p.author_name, p.is_notice, p.is_secret, p.is_hidden, p.view_count, p.comment_count, p.attachment_count, p.created_at, p.updated_at,
                b.board_key, b.board_name, b.board_write_level, b.author_display_mode
         FROM " . smartcms_table('board_posts') . " p
         INNER JOIN " . smartcms_table('boards') . " b ON b.id = p.board_id
         WHERE p.board_id = :board_id AND p.is_hidden = 0 AND p.id IN (" . implode(', ', $placeholders) . ")
         ORDER BY p.is_notice DESC, p.id ASC"
    );
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function smartcms_board_copy_editor_content_to_board(string $content, array $source_board, array $target_board, bool $duplicate_same_board = true): array
{
    $content = (string)$content;
    $source_dir = realpath(smartcms_board_editor_upload_dir($source_board)) ?: smartcms_board_editor_upload_dir($source_board);
    $source_dir = rtrim(str_replace('\\', '/', $source_dir), '/');
    $target_dir = smartcms_board_editor_upload_dir($target_board);
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    $target_dir = rtrim(str_replace('\\', '/', $target_dir), '/');

    $source_key = smartcms_board_key((string)($source_board['board_key'] ?? ''));
    $target_key = smartcms_board_key((string)($target_board['board_key'] ?? ''));
    if ($source_key === $target_key && !$duplicate_same_board) {
        return ['content' => $content, 'files' => []];
    }

    $replace_map = [];
    $copied_paths = [];
    foreach (smartcms_image_extract_sources_from_html($content) as $source_url) {
        if (isset($replace_map[$source_url])) {
            continue;
        }

        $source_path = smartcms_image_source_path_from_url($source_url);
        if ($source_path === null) {
            continue;
        }

        $normalized = rtrim(str_replace('\\', '/', $source_path), '/');
        if (!str_starts_with($normalized, $source_dir . '/')) {
            continue;
        }

        if (!is_file($source_path)) {
            continue;
        }

        $extension = strtolower(pathinfo($source_path, PATHINFO_EXTENSION));
        $stored_name = date('YmdHis') . '_' . bin2hex(random_bytes(8));
        if ($extension !== '') {
            $stored_name .= '.' . $extension;
        }

        $target_path = $target_dir . '/' . $stored_name;
        if (!copy($source_path, $target_path)) {
            continue;
        }

        $replace_map[$source_url] = smartcms_board_editor_upload_url($target_board, $stored_name);
        $copied_paths[] = $source_path;
    }

    if (!$replace_map) {
        return ['content' => $content, 'files' => []];
    }

    return [
        'content' => strtr($content, $replace_map),
        'files' => array_values(array_unique($copied_paths)),
    ];
}

function smartcms_board_copy_post_files(array $source_files, array $target_board, int $target_post_id, int $uploaded_by): int
{
    if (!$source_files) {
        return 0;
    }

    $copied = 0;
    $upload_root = SMARTCMS_ROOT . '/uploads/board';
    if (!is_dir($upload_root)) {
        mkdir($upload_root, 0755, true);
    }

    foreach ($source_files as $file) {
        $source_path = SMARTCMS_ROOT . '/' . ltrim((string)$file['file_path'], '/');
        if (!is_file($source_path)) {
            continue;
        }

        $extension = strtolower(pathinfo((string)$file['stored_name'], PATHINFO_EXTENSION));
        $stored_name = date('YmdHis') . '_' . bin2hex(random_bytes(8));
        if ($extension !== '') {
            $stored_name .= '.' . $extension;
        }

        $target_path = $upload_root . '/' . $stored_name;
        if (!copy($source_path, $target_path)) {
            continue;
        }

        smartcms_execute(
            "INSERT INTO " . smartcms_table('board_files') . "
             (board_id, post_id, comment_id, original_name, stored_name, file_path, file_size, mime_type, download_count, uploaded_by)
             VALUES (:board_id, :post_id, NULL, :original_name, :stored_name, :file_path, :file_size, :mime_type, 0, :uploaded_by)",
            [
                'board_id' => (int)$target_board['id'],
                'post_id' => $target_post_id,
                'original_name' => (string)$file['original_name'],
                'stored_name' => $stored_name,
                'file_path' => 'uploads/board/' . $stored_name,
                'file_size' => (int)($file['file_size'] ?? 0),
                'mime_type' => (string)($file['mime_type'] ?? null),
                'uploaded_by' => $uploaded_by,
            ]
        );
        $copied++;
    }

    if ($copied > 0) {
        smartcms_execute(
            "UPDATE " . smartcms_table('board_posts') . " SET attachment_count = attachment_count + :count WHERE id = :id",
            ['count' => $copied, 'id' => $target_post_id]
        );
    }

    return $copied;
}

function smartcms_board_post_counts(): array
{
    $stmt = smartcms_db()->query(
        "SELECT b.board_key, COUNT(p.id) AS post_count
         FROM " . smartcms_table('boards') . " b
         LEFT JOIN " . smartcms_table('board_posts') . " p ON p.board_id = b.id AND p.is_hidden = 0
         WHERE b.status <> 'disabled'
         GROUP BY b.board_key"
    );
    $counts = [];

    foreach ($stmt->fetchAll() as $row) {
        $counts[(string)$row['board_key']] = (int)$row['post_count'];
    }

    return $counts;
}

function smartcms_board_create_post(array $board, array $user, string $title, string $link_url_1, string $link_url_2, string $content, string $content_mode = 'text', bool $is_notice = false, bool $is_secret = false): array
{
    smartcms_ensure_board_posts_content_mode_column();
    smartcms_ensure_board_posts_link_column();
    $title = trim($title);
    $link_url_1 = smartcms_board_normalize_link_url($link_url_1);
    $link_url_2 = smartcms_board_normalize_link_url($link_url_2);
    $content = trim($content);
    $content_mode = smartcms_board_normalize_content_mode($content_mode);
    if ($title === '' || $content === '') {
        return ['ok' => false, 'message' => '제목과 내용을 입력하세요.'];
    }

    smartcms_execute(
        "INSERT INTO " . smartcms_table('board_posts') . "
         (board_id, title, link_url, link_url_1, link_url_2, content, content_mode, excerpt, author_id, author_name, is_notice, is_secret)
         VALUES (:board_id, :title, :link_url, :link_url_1, :link_url_2, :content, :content_mode, :excerpt, :author_id, :author_name, :is_notice, :is_secret)",
        [
            'board_id' => (int)$board['id'],
            'title' => $title,
            'link_url' => $link_url_1 !== '' ? $link_url_1 : null,
            'link_url_1' => $link_url_1 !== '' ? $link_url_1 : null,
            'link_url_2' => $link_url_2 !== '' ? $link_url_2 : null,
            'content' => $content,
            'content_mode' => $content_mode,
            'excerpt' => smartcms_board_excerpt($content),
            'author_id' => (int)$user['id'],
            'author_name' => (string)$user['name'],
            'is_notice' => $is_notice ? 1 : 0,
            'is_secret' => $is_secret ? 1 : 0,
        ]
    );

    $post = [
        'id' => (int)smartcms_db()->lastInsertId(),
    ];
    smartcms_board_audit($board, $post, $user, 'post_create', '게시글을 등록했습니다.');
    return ['ok' => true, 'message' => '글을 등록했습니다.', 'post_id' => $post['id']];
}

function smartcms_board_store_uploads(array $board, int $post_id, array $user, array $files): array
{
    if (empty($files['name']) || (int)($board['use_attachments'] ?? 1) !== 1) {
        return ['ok' => true, 'message' => '첨부할 파일이 없습니다.', 'count' => 0];
    }

    if (!smartcms_has_level((int)($board['board_upload_level'] ?? 8), $user)) {
        return ['ok' => false, 'message' => '첨부파일 업로드 권한이 없습니다.', 'count' => 0];
    }

    $names = is_array($files['name']) ? $files['name'] : [$files['name']];
    $tmp_names = is_array($files['tmp_name']) ? $files['tmp_name'] : [$files['tmp_name']];
    $sizes = is_array($files['size']) ? $files['size'] : [$files['size']];
    $errors = is_array($files['error']) ? $files['error'] : [$files['error']];
    $types = is_array($files['type']) ? $files['type'] : [$files['type']];
    $stored_count = 0;
    $upload_root = SMARTCMS_ROOT . '/uploads/board';

    if (!is_dir($upload_root)) {
        mkdir($upload_root, 0755, true);
    }

    foreach ($names as $index => $name) {
        if ((int)$errors[$index] === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if ((int)$errors[$index] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'message' => '파일 업로드 중 오류가 발생했습니다.', 'count' => $stored_count];
        }

        $size = (int)$sizes[$index];
        $max_mb = max(1, smartcms_setting_int('upload_max_mb', 10));
        if ($size > $max_mb * 1024 * 1024) {
            return ['ok' => false, 'message' => '첨부파일은 ' . $max_mb . 'MB 이하만 업로드할 수 있습니다.', 'count' => $stored_count];
        }

        $original_name = basename((string)$name);
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $mime = substr((string)$types[$index], 0, 120);
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $detected_mime = (string)finfo_file($finfo, (string)$tmp_names[$index]);
                finfo_close($finfo);
                if ($detected_mime !== '') {
                    $mime = substr($detected_mime, 0, 120);
                }
            }
        }
        $stored_name = date('YmdHis') . '_' . bin2hex(random_bytes(8)) . ($extension !== '' ? '.' . $extension : '');
        $target_path = $upload_root . '/' . $stored_name;

        if (!move_uploaded_file((string)$tmp_names[$index], $target_path)) {
            return ['ok' => false, 'message' => '첨부파일 저장에 실패했습니다.', 'count' => $stored_count];
        }

        if (preg_match('#^image/(jpeg|png|gif|webp)$#i', $mime)) {
            smartcms_image_resize_file($target_path, $target_path);
        }

        smartcms_execute(
            "INSERT INTO " . smartcms_table('board_files') . "
             (board_id, post_id, original_name, stored_name, file_path, file_size, mime_type, uploaded_by)
             VALUES (:board_id, :post_id, :original_name, :stored_name, :file_path, :file_size, :mime_type, :uploaded_by)",
            [
                'board_id' => (int)$board['id'],
                'post_id' => $post_id,
                'original_name' => $original_name,
                'stored_name' => $stored_name,
                'file_path' => 'uploads/board/' . $stored_name,
                'file_size' => $size,
                'mime_type' => substr((string)$types[$index], 0, 120),
                'uploaded_by' => (int)$user['id'],
            ]
        );
        $stored_count++;
    }

    if ($stored_count > 0) {
        smartcms_execute(
            "UPDATE " . smartcms_table('board_posts') . " SET attachment_count = attachment_count + :count WHERE id = :id",
            [
                'count' => $stored_count,
                'id' => $post_id,
            ]
        );
        smartcms_board_audit($board, ['id' => $post_id], $user, 'file_upload', '첨부파일을 업로드했습니다.');
    }

    return ['ok' => true, 'message' => '첨부파일을 저장했습니다.', 'count' => $stored_count];
}

function smartcms_board_delete_uploads(array $board, array $post, array $user, array $file_ids): array
{
    if (empty($file_ids)) {
        return ['ok' => true, 'message' => '삭제할 첨부파일이 없습니다.', 'count' => 0];
    }

    if (!smartcms_board_can_manage_post($board, $post, $user)) {
        return ['ok' => false, 'message' => '첨부파일 삭제 권한이 없습니다.', 'count' => 0];
    }

    $file_ids = array_values(array_unique(array_filter(array_map('intval', is_array($file_ids) ? $file_ids : [$file_ids]), static function (int $file_id): bool {
        return $file_id > 0;
    })));

    if (!$file_ids) {
        return ['ok' => true, 'message' => '삭제할 첨부파일이 없습니다.', 'count' => 0];
    }

    $placeholders = [];
    $params = [
        'board_id' => (int)$board['id'],
        'post_id' => (int)$post['id'],
    ];

    foreach ($file_ids as $index => $file_id) {
        $key = 'file_id_' . $index;
        $placeholders[] = ':' . $key;
        $params[$key] = $file_id;
    }

    $stmt = smartcms_db()->prepare(
        "SELECT id, file_path
         FROM " . smartcms_table('board_files') . "
         WHERE board_id = :board_id AND post_id = :post_id AND id IN (" . implode(', ', $placeholders) . ")
         ORDER BY id ASC"
    );
    $stmt->execute($params);
    $files = $stmt->fetchAll();

    if (count($files) !== count($file_ids)) {
        return ['ok' => false, 'message' => '삭제할 첨부파일을 찾지 못했습니다.', 'count' => 0];
    }

    $db = smartcms_db();
    $db->beginTransaction();

    try {
        $delete_stmt = $db->prepare(
            "DELETE FROM " . smartcms_table('board_files') . "
             WHERE id = :id AND board_id = :board_id AND post_id = :post_id"
        );
        $decrement_stmt = $db->prepare(
            "UPDATE " . smartcms_table('board_posts') . "
             SET attachment_count = CASE WHEN attachment_count > 0 THEN attachment_count - 1 ELSE 0 END
             WHERE id = :id"
        );

        foreach ($files as $file) {
            $delete_stmt->execute([
                'id' => (int)$file['id'],
                'board_id' => (int)$board['id'],
                'post_id' => (int)$post['id'],
            ]);
            $decrement_stmt->execute(['id' => (int)$post['id']]);
        }

        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }

        return ['ok' => false, 'message' => '첨부파일 삭제 중 오류가 발생했습니다.', 'count' => 0];
    }

    foreach ($files as $file) {
        $path = SMARTCMS_ROOT . '/' . ltrim((string)$file['file_path'], '/');
        if (is_file($path)) {
            smartcms_image_delete_thumbnail_cache_for_source($path);
            @unlink($path);
        }
    }

    smartcms_board_audit($board, $post, $user, 'file_delete', '첨부파일을 삭제했습니다.');
    return ['ok' => true, 'message' => '첨부파일을 삭제했습니다.', 'count' => count($files)];
}
