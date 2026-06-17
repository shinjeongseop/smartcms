<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/board.php';

header('Content-Type: application/json; charset=UTF-8');

function smartcms_webhook_json_response(int $status_code, array $payload): never
{
    http_response_code($status_code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function smartcms_webhook_setting(string $config_key, string $env_key, string $default = ''): string
{
    $value = trim((string)smartcms_config_value($config_key, ''));
    if ($value !== '') {
        return $value;
    }

    $value = trim((string)getenv($env_key));
    return $value !== '' ? $value : $default;
}

function smartcms_webhook_request_token(): string
{
    $header_token = trim((string)($_SERVER['HTTP_X_SMARTCMS_WEBHOOK_TOKEN'] ?? ''));
    if ($header_token !== '') {
        return $header_token;
    }

    $authorization = trim((string)($_SERVER['HTTP_AUTHORIZATION'] ?? ''));
    if ($authorization !== '' && preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches) === 1) {
        return trim((string)$matches[1]);
    }

    return '';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    smartcms_webhook_json_response(405, [
        'ok' => false,
        'message' => 'POST 요청만 허용됩니다.',
    ]);
}

$expected_token = smartcms_webhook_setting(
    'webhooks.github_commit_log.token',
    'SMARTCMS_WEBHOOK_TOKEN'
);
if ($expected_token === '') {
    smartcms_webhook_json_response(500, [
        'ok' => false,
        'message' => '웹훅 토큰이 설정되지 않았습니다.',
    ]);
}

$request_token = smartcms_webhook_request_token();
if ($request_token === '' || !hash_equals($expected_token, $request_token)) {
    smartcms_webhook_json_response(401, [
        'ok' => false,
        'message' => '웹훅 인증에 실패했습니다.',
    ]);
}

$raw_input = file_get_contents('php://input');
$payload = is_string($raw_input) && trim($raw_input) !== ''
    ? json_decode($raw_input, true)
    : null;

if (!is_array($payload)) {
    smartcms_webhook_json_response(400, [
        'ok' => false,
        'message' => 'JSON 요청 본문이 필요합니다.',
    ]);
}

$payload_board_key = smartcms_board_key((string)($payload['board_key'] ?? ''));
$board_key = $payload_board_key !== ''
    ? $payload_board_key
    : smartcms_board_key(smartcms_webhook_setting(
        'webhooks.github_commit_log.board_key',
        'SMARTCMS_WEBHOOK_BOARD_KEY'
    ));
if ($board_key === '') {
    smartcms_webhook_json_response(400, [
        'ok' => false,
        'message' => '대상 게시판 키가 필요합니다.',
    ]);
}

$board = smartcms_board_find($board_key);
if (!$board) {
    smartcms_webhook_json_response(404, [
        'ok' => false,
        'message' => '대상 게시판을 찾을 수 없습니다.',
    ]);
}

$commits = isset($payload['commits']) && is_array($payload['commits']) ? $payload['commits'] : [];
if (!$commits) {
    smartcms_webhook_json_response(400, [
        'ok' => false,
        'message' => '커밋 목록이 필요합니다.',
    ]);
}

$repository = trim((string)($payload['repository'] ?? ''));
$branch = trim((string)($payload['branch'] ?? ''));
$before = trim((string)($payload['before'] ?? ''));
$after = trim((string)($payload['after'] ?? ''));
$compare_url = trim((string)($payload['compare_url'] ?? ''));
$author_name = smartcms_webhook_setting(
    'webhooks.github_commit_log.author_name',
    'SMARTCMS_WEBHOOK_AUTHOR_NAME',
    'GitHub Actions'
);

$commit_count = count($commits);
$title = trim((string)($payload['title'] ?? ''));
if ($title === '') {
    $title = trim(($branch !== '' ? '[' . $branch . '] ' : '') . '커밋 ' . $commit_count . '건 자동 등록');
}
if ($title === '') {
    $title = '커밋 자동 등록';
}
$title = function_exists('mb_substr') ? mb_substr($title, 0, 255, 'UTF-8') : substr($title, 0, 255);

$content_lines = [];
$content_lines[] = 'GitHub Actions 커밋 자동 등록';
if ($repository !== '') {
    $content_lines[] = '- 저장소: `' . $repository . '`';
}
if ($branch !== '') {
    $content_lines[] = '- 브랜치: `' . $branch . '`';
}
if ($before !== '' || $after !== '') {
    $content_lines[] = '- 범위: `' . ($before !== '' ? $before : '-') . ' → ' . ($after !== '' ? $after : '-') . '`';
}
if ($compare_url !== '') {
    $content_lines[] = '- 비교 링크: ' . $compare_url;
}
$content_lines[] = '- 커밋 수: `' . $commit_count . '`';
$content_lines[] = '';
$content_lines[] = '커밋 목록';

$max_items = 20;
foreach (array_slice($commits, 0, $max_items) as $commit) {
    if (!is_array($commit)) {
        continue;
    }

    $sha = substr(trim((string)($commit['sha'] ?? '')), 0, 7);
    $message = trim((string)($commit['message'] ?? ''));
    $author = trim((string)($commit['author'] ?? ''));
    $line = '- ';
    if ($sha !== '') {
        $line .= '`' . $sha . '` ';
    }
    $line .= $message !== '' ? $message : '메시지 없음';
    if ($author !== '') {
        $line .= ' - ' . $author;
    }
    $content_lines[] = $line;
}

if ($commit_count > $max_items) {
    $content_lines[] = '- 외 ' . ($commit_count - $max_items) . '건 생략';
}

$content = implode("\n", $content_lines);
$result = smartcms_board_create_post($board, null, $title, '', '', $content, 'text', false, false, $author_name);
if (empty($result['ok'])) {
    smartcms_webhook_json_response(500, [
        'ok' => false,
        'message' => (string)($result['message'] ?? '게시글 등록에 실패했습니다.'),
    ]);
}

smartcms_webhook_json_response(200, [
    'ok' => true,
    'message' => '게시판에 커밋 로그를 등록했습니다.',
    'board_key' => $board_key,
    'post_id' => (int)($result['post_id'] ?? 0),
    'post_url' => smartcms_board_post_url($board_key, (int)($result['post_id'] ?? 0)),
]);
