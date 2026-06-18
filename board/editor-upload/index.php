<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/board.php';

header('Content-Type: application/json; charset=utf-8');

$board_key = smartcms_board_key((string)($_POST['code'] ?? $_POST['board'] ?? $_POST['board_code'] ?? $_GET['code'] ?? $_GET['board'] ?? ''));
$board = $board_key !== '' ? smartcms_board_find($board_key) : null;

if (!$board) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 1, 'message' => '게시판을 찾을 수 없습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$user = smartcms_current_user();
if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 1, 'message' => '로그인이 필요합니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ((string)($board['status'] ?? 'active') === 'disabled' || (string)($board['permission_status'] ?? 'active') === 'disabled') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 1, 'message' => '사용할 수 없는 게시판입니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!smartcms_has_level((int)($board['board_write_level'] ?? 0), $user) || !smartcms_has_level((int)($board['board_upload_level'] ?? 0), $user)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 1, 'message' => '이미지 업로드 권한이 없습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!smartcms_verify_csrf()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 1, 'message' => 'CSRF 검증에 실패했습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$files = $_FILES['files'] ?? null;
if (!$files || empty($files['name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 1, 'message' => '업로드할 파일이 없습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$names = is_array($files['name']) ? $files['name'] : [$files['name']];
$tmp_names = is_array($files['tmp_name']) ? $files['tmp_name'] : [$files['tmp_name']];
$sizes = is_array($files['size']) ? $files['size'] : [$files['size']];
$errors = is_array($files['error']) ? $files['error'] : [$files['error']];

$upload_dir = smartcms_board_editor_upload_dir($board);
if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 1, 'message' => '업로드 폴더를 만들 수 없습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$stored_urls = [];
$max_mb = max(1, smartcms_setting_int('upload_max_mb', 10));
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'avif'];
$allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/avif'];

foreach ($names as $index => $name) {
    $error = (int)($errors[$index] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE) {
        continue;
    }
    if ($error !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 1, 'message' => '이미지 업로드 중 오류가 발생했습니다.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $tmp_name = (string)($tmp_names[$index] ?? '');
    if ($tmp_name === '' || !is_uploaded_file($tmp_name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 1, 'message' => '업로드된 파일을 확인할 수 없습니다.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $size = (int)($sizes[$index] ?? 0);
    if ($size <= 0 || $size > $max_mb * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 1, 'message' => '이미지는 ' . $max_mb . 'MB 이하만 업로드할 수 있습니다.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $original_name = basename((string)$name);
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_extensions, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 1, 'message' => '허용되지 않은 이미지 형식입니다.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $mime = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $mime = (string)finfo_file($finfo, $tmp_name);
            finfo_close($finfo);
        }
    }
    if ($mime === '' && isset($files['type'][$index])) {
        $mime = (string)$files['type'][$index];
    }
    if ($mime === '' || !in_array(strtolower($mime), $allowed_mimes, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 1, 'message' => '이미지 파일만 업로드할 수 있습니다.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $safe_extension = $extension === 'jpeg' ? 'jpg' : $extension;
    $stored_name = date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $safe_extension;
    $target_path = $upload_dir . '/' . $stored_name;

    if (!move_uploaded_file($tmp_name, $target_path)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 1, 'message' => '이미지를 저장할 수 없습니다.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    smartcms_image_resize_file_to_width($target_path, $target_path, smartcms_board_editor_image_max_width($board));

    $stored_urls[] = smartcms_board_editor_upload_url($board, $stored_name);
}

echo json_encode([
    'success' => true,
    'error' => 0,
    'message' => '',
    'files' => $stored_urls,
    'isImages' => array_fill(0, count($stored_urls), true),
    'path' => '',
    'baseurl' => '',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
