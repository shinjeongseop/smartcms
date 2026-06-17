<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/config.php';

header('Content-Type: application/json; charset=UTF-8');

$webhook_local = __DIR__ . '/../../webhook.local.php';
$webhook_local_config = is_file($webhook_local) ? (array)require $webhook_local : [];

function smartcms_webhook_board_key(string $value): string
{
    return preg_replace('/[^a-zA-Z0-9_-]/', '', trim($value));
}

function smartcms_webhook_json_response(int $status_code, array $payload): never
{
    http_response_code($status_code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function smartcms_webhook_setting(string $config_key, string $env_key, string $default = ''): string
{
    global $webhook_local_config;

    $value = trim((string)($webhook_local_config[$config_key] ?? ''));
    if ($value !== '') {
        return $value;
    }

    $path = explode('.', $config_key, 2);
    if (count($path) === 2) {
        $group = trim($path[0]);
        $name = trim($path[1]);
        if ($group !== '' && $name !== '' && isset($webhook_local_config[$group]) && is_array($webhook_local_config[$group])) {
            $value = trim((string)($webhook_local_config[$group][$name] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }
    }

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
    'github_commit_log.token',
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

try {
    require_once __DIR__ . '/../../common/board.php';
    require_once __DIR__ . '/../../common/schema.php';

    smartcms_create_schema();
} catch (Throwable $e) {
    // 필요한 테이블이 이미 있으면 계속 진행한다.
}

try {
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

    $payload_board_key = smartcms_webhook_board_key((string)($payload['board_key'] ?? ''));
    $board_key = $payload_board_key !== ''
        ? $payload_board_key
        : smartcms_webhook_board_key(smartcms_webhook_setting(
            'github_commit_log.board_key',
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

    $author_name = smartcms_webhook_setting(
        'github_commit_log.author_name',
        'SMARTCMS_WEBHOOK_AUTHOR_NAME',
        'GitHub Actions'
    );

    $commit_count = count($commits);
    if ($commit_count === 1) {
        $first_message = trim((string)($commits[0]['message'] ?? ''));
        $title = $first_message !== '' ? $first_message : '커밋 로그';
    } elseif ($commit_count > 1) {
        $first_message = trim((string)($commits[0]['message'] ?? ''));
        $title = $first_message !== ''
            ? $first_message . ' 외 ' . ($commit_count - 1) . '건 변경'
            : '커밋 로그';
    } else {
        $title = '커밋 로그';
    }
    $title = function_exists('mb_substr') ? mb_substr($title, 0, 255, 'UTF-8') : substr($title, 0, 255);

    $content_lines = [];
    $content_lines[] = '커밋 상세';

    $max_items = 20;
    foreach (array_slice($commits, 0, $max_items) as $index => $commit) {
        if (!is_array($commit)) {
            continue;
        }

        $message = trim((string)($commit['message'] ?? ''));
        $short_sha = substr(trim((string)($commit['short_sha'] ?? $commit['sha'] ?? '')), 0, 7);
        $files = isset($commit['files']) && is_array($commit['files']) ? $commit['files'] : [];
        $files = array_values(array_filter(array_map('trim', $files), static fn(string $file): bool => $file !== ''));

        $content_lines[] = ($index + 1) . '. 해결 내용: ' . ($message !== '' ? $message : '메시지 없음');
        if ($short_sha !== '') {
            $content_lines[] = '   - SHA: ' . $short_sha;
        }
        if ($files) {
            $content_lines[] = '   - 수정 파일: ' . implode(', ', $files);
        } else {
            $content_lines[] = '   - 수정 파일: 없음';
        }
        if ($index < min($max_items, count($commits)) - 1) {
            $content_lines[] = '';
        }
    }

    if ($commit_count > $max_items) {
        $content_lines[] = '';
        $content_lines[] = '- 외 ' . ($commit_count - $max_items) . '건 생략';
    }

    $content = trim(implode("\n", $content_lines));
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
} catch (Throwable $e) {
    smartcms_webhook_json_response(500, [
        'ok' => false,
        'message' => '웹훅 처리 중 오류가 발생했습니다.',
        'error' => $e->getMessage(),
        'exception' => get_class($e),
    ]);
}
