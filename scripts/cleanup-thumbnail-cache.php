<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/board.php';

$mode = in_array('--all', $argv ?? [], true) ? 'all' : 'legacy';
$result = smartcms_image_cleanup_thumbnail_cache($mode);
$message = $mode === 'all' ? '썸네일 캐시 전체 정리 완료' : '구버전 썸네일 캐시 정리 완료';
$deleted_files = (int)($result['deleted_files'] ?? 0);
$deleted_dirs = (int)($result['deleted_dirs'] ?? 0);
$deleted_total = $deleted_files + $deleted_dirs;

if (PHP_SAPI === 'cli') {
    echo $message . PHP_EOL;
    echo '삭제 항목 수: ' . $deleted_total . PHP_EOL;
    echo '삭제 파일 수: ' . $deleted_files . PHP_EOL;
    echo '삭제 폴더 수: ' . $deleted_dirs . PHP_EOL;
    $removed_dirs = $result['removed_dirs'] ?? [];
    if (is_array($removed_dirs) && $removed_dirs) {
        echo '삭제한 디렉토리: ' . implode(', ', $removed_dirs) . PHP_EOL;
    }
    exit(0);
}

header('Content-Type: text/plain; charset=utf-8');
echo $message . "\n";
echo '삭제 항목 수: ' . $deleted_total . "\n";
echo '삭제 파일 수: ' . $deleted_files . "\n";
echo '삭제 폴더 수: ' . $deleted_dirs . "\n";
$removed_dirs = $result['removed_dirs'] ?? [];
if (is_array($removed_dirs) && $removed_dirs) {
    echo '삭제한 디렉토리: ' . implode(', ', $removed_dirs) . "\n";
}
