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

function smartcms_board_excerpt(string $content, int $length = 200): string
{
    $plain = strip_tags($content);
    if (function_exists('mb_substr')) {
        return mb_substr($plain, 0, $length);
    }

    return substr($plain, 0, $length);
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

function smartcms_board_posts(int $board_id, int $limit = 30): array
{
    $stmt = smartcms_db()->prepare(
        "SELECT id, title, author_name, is_notice, is_secret, view_count, comment_count, created_at
         FROM " . smartcms_table('board_posts') . "
         WHERE board_id = :board_id AND is_hidden = 0
         ORDER BY is_notice DESC, id DESC
         LIMIT :limit"
    );
    $stmt->bindValue('board_id', $board_id, PDO::PARAM_INT);
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
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

    return ['ok' => true, 'message' => '댓글을 등록했습니다.'];
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

    return ['ok' => true, 'message' => '글을 등록했습니다.'];
}
