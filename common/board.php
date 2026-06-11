<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

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
        'table' => ['label' => '테이블', 'accent' => 'secondary', 'layout' => 'table', 'icon' => 'bi-table'],
        'card' => ['label' => '카드', 'accent' => 'success', 'layout' => 'cards', 'icon' => 'bi-postcard-heart-fill'],
        'gallery' => ['label' => '갤러리', 'accent' => 'info', 'layout' => 'cards', 'icon' => 'bi-grid-3x3-gap-fill'],
        'qna' => ['label' => 'Q&A', 'accent' => 'warning', 'layout' => 'table', 'icon' => 'bi-question-circle-fill'],
        'notice' => ['label' => '공지', 'accent' => 'danger', 'layout' => 'table', 'icon' => 'bi-megaphone-fill'],
        'faq' => ['label' => 'FAQ', 'accent' => 'dark', 'layout' => 'cards', 'icon' => 'bi-patch-question-fill'],
        'webzine' => ['label' => '웹진', 'accent' => 'primary', 'layout' => 'cards', 'icon' => 'bi-journal-richtext'],
    ];

    $meta = $meta_map[$skin] ?? $meta_map['default'];
    $meta['skin'] = $skin;
    $meta['badge_class'] = 'bg-' . $meta['accent'] . '-subtle text-' . ($meta['accent'] === 'dark' ? 'dark' : $meta['accent']) . ' border border-' . $meta['accent'] . '-subtle';
    $meta['button_class'] = $meta['accent'] === 'dark' ? 'btn-dark' : 'btn-' . $meta['accent'];
    $meta['button_text_class'] = in_array($meta['accent'], ['warning', 'info'], true) ? 'text-dark' : 'text-white';
    $meta['header_class'] = 'border-top border-4 border-' . $meta['accent'];

    return $meta;
}

function smartcms_board_title_limit(array $board): int
{
    return max(0, (int)($board['title_length_limit'] ?? 0));
}

function smartcms_board_truncate_title(string $title, int $length): string
{
    $title = trim(strip_tags($title));
    if ($length <= 0 || $title === '') {
        return $title;
    }

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($title) <= $length) {
            return $title;
        }

        return mb_substr($title, 0, $length) . '...';
    }

    if (strlen($title) <= $length) {
        return $title;
    }

    return substr($title, 0, $length) . '...';
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

function smartcms_board_list(): array
{
    $stmt = smartcms_db()->query(
        "SELECT b.*, p.board_list_level, p.board_view_level, p.board_write_level, p.allow_guest_list, p.allow_guest_view
         FROM " . smartcms_table('boards') . " b
         LEFT JOIN " . smartcms_table('board_permissions') . " p ON p.board_key = b.board_key
         WHERE b.status <> 'disabled'
         ORDER BY b.id DESC"
    );

    return $stmt->fetchAll();
}

function smartcms_board_find(string $board_key): ?array
{
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
        http_response_code(403);
        echo 'Board disabled.';
        exit;
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
        http_response_code(403);
        echo 'Permission denied.';
        exit;
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
    $where = "board_id = :board_id AND is_hidden = 0";
    $params = ['board_id' => $board_id];

    if ($keyword !== '') {
        $where .= " AND (title LIKE :keyword_title OR content LIKE :keyword_content OR author_name LIKE :keyword_author)";
        $params['keyword_title'] = '%' . $keyword . '%';
        $params['keyword_content'] = '%' . $keyword . '%';
        $params['keyword_author'] = '%' . $keyword . '%';
    }

    $count_stmt = smartcms_db()->prepare(
        "SELECT COUNT(*) AS cnt
         FROM " . smartcms_table('board_posts') . "
         WHERE {$where}"
    );
    $count_stmt->execute($params);
    $total = (int)($count_stmt->fetch()['cnt'] ?? 0);

    $stmt = smartcms_db()->prepare(
        "SELECT id, title, author_name, is_notice, is_secret, view_count, comment_count, attachment_count, created_at
         FROM " . smartcms_table('board_posts') . "
         WHERE {$where}
         ORDER BY is_notice DESC, id DESC
         LIMIT :limit OFFSET :offset"
    );
    $stmt->bindValue('board_id', $board_id, PDO::PARAM_INT);
    if ($keyword !== '') {
        $like = '%' . $keyword . '%';
        $stmt->bindValue('keyword_title', $like, PDO::PARAM_STR);
        $stmt->bindValue('keyword_content', $like, PDO::PARAM_STR);
        $stmt->bindValue('keyword_author', $like, PDO::PARAM_STR);
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
    return smartcms_fetch_one(
        "SELECT id, board_id, title, content, author_id, author_name, is_notice, is_secret, view_count, comment_count, created_at, updated_at
         FROM " . smartcms_table('board_posts') . "
         WHERE board_id = :board_id AND id = :id AND is_hidden = 0
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
        "SELECT id, original_name, file_size, download_count, created_at
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

function smartcms_board_update_post(array $board, array $post, array $user, string $title, string $content, bool $is_notice, bool $is_secret): array
{
    if (!smartcms_board_can_manage_post($board, $post, $user)) {
        return ['ok' => false, 'message' => '글 수정 권한이 없습니다.'];
    }

    $title = trim($title);
    $content = trim($content);
    if ($title === '' || $content === '') {
        return ['ok' => false, 'message' => '제목과 내용을 입력하세요.'];
    }

    $can_notice = smartcms_has_level((int)($board['board_manage_level'] ?? 8), $user);
    smartcms_execute(
        "UPDATE " . smartcms_table('board_posts') . "
         SET title = :title,
             content = :content,
             excerpt = :excerpt,
             is_notice = :is_notice,
             is_secret = :is_secret
         WHERE id = :id AND board_id = :board_id",
        [
            'id' => (int)$post['id'],
            'board_id' => (int)$board['id'],
            'title' => $title,
            'content' => $content,
            'excerpt' => smartcms_board_excerpt($content),
            'is_notice' => $can_notice && $is_notice ? 1 : 0,
            'is_secret' => $is_secret ? 1 : 0,
        ]
    );

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

function smartcms_board_create(string $board_key, string $board_name, string $description, int $created_by): array
{
    $board_key = smartcms_board_key($board_key);
    $board_name = trim($board_name);

    if ($board_key === '' || $board_name === '') {
        return ['ok' => false, 'message' => '게시판 키와 이름을 입력하세요.'];
    }

    $exists = smartcms_board_find($board_key);
    if ($exists) {
        return ['ok' => false, 'message' => '이미 존재하는 게시판 키입니다.'];
    }

    smartcms_execute(
        "INSERT INTO " . smartcms_table('boards') . "
         (board_key, board_name, description, created_by)
         VALUES (:board_key, :board_name, :description, :created_by)",
        [
            'board_key' => $board_key,
            'board_name' => $board_name,
            'description' => trim($description) !== '' ? trim($description) : null,
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
        "SELECT p.id, p.title, p.author_name, p.comment_count, p.attachment_count, p.created_at, b.board_key, b.board_name, b.title_length_limit
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
        "SELECT p.id, p.title, p.author_name, p.comment_count, p.attachment_count, p.view_count, p.created_at,
                b.board_key, b.board_name, b.title_length_limit
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
        "SELECT p.id, p.title, p.author_name, p.comment_count, p.view_count, p.created_at, b.board_key, b.board_name, b.title_length_limit
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

function smartcms_board_create_post(array $board, array $user, string $title, string $content, bool $is_notice = false, bool $is_secret = false): array
{
    $title = trim($title);
    $content = trim($content);
    if ($title === '' || $content === '') {
        return ['ok' => false, 'message' => '제목과 내용을 입력하세요.'];
    }

    smartcms_execute(
        "INSERT INTO " . smartcms_table('board_posts') . "
         (board_id, title, content, excerpt, author_id, author_name, is_notice, is_secret)
         VALUES (:board_id, :title, :content, :excerpt, :author_id, :author_name, :is_notice, :is_secret)",
        [
            'board_id' => (int)$board['id'],
            'title' => $title,
            'content' => $content,
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
        $stored_name = date('YmdHis') . '_' . bin2hex(random_bytes(8)) . ($extension !== '' ? '.' . $extension : '');
        $target_path = $upload_root . '/' . $stored_name;

        if (!move_uploaded_file((string)$tmp_names[$index], $target_path)) {
            return ['ok' => false, 'message' => '첨부파일 저장에 실패했습니다.', 'count' => $stored_count];
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
