<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/board.php';

$file = smartcms_board_file_find((int)($_GET['file'] ?? 0));

if (!$file || (int)$file['is_hidden'] === 1) {
    http_response_code(404);
    echo 'File not found.';
    exit;
}

$board = [
    'id' => (int)$file['board_id'],
    'board_key' => (string)$file['board_key'],
    'status' => (string)$file['board_status'],
    'permission_status' => (string)($file['permission_status'] ?? 'active'),
    'board_view_level' => (int)($file['board_view_level'] ?? 0),
    'board_manage_level' => (int)($file['board_manage_level'] ?? 8),
    'allow_guest_view' => (int)($file['allow_guest_view'] ?? 1),
];
$user = smartcms_require_board_access($board, 'view');

if ((int)$file['is_secret'] === 1 && (!$user || ((int)$file['author_id'] !== (int)$user['id'] && !smartcms_has_level((int)$board['board_manage_level'], $user)))) {
    http_response_code(403);
    echo 'Permission denied.';
    exit;
}

$path = SMARTCMS_ROOT . '/' . ltrim((string)$file['file_path'], '/');
if (!is_file($path)) {
    http_response_code(404);
    echo 'File missing.';
    exit;
}

if (smartcms_board_should_count_download($board, $file, $user) && smartcms_board_count_once('download', (int)$file['id'], 0)) {
    smartcms_execute(
        "UPDATE " . smartcms_table('board_files') . " SET download_count = download_count + 1 WHERE id = :id",
        ['id' => (int)$file['id']]
    );
}
smartcms_board_audit($board, ['id' => (int)$file['post_id']], $user, 'file_download', '첨부파일을 다운로드했습니다.');

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . rawurlencode((string)$file['original_name']) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
