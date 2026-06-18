<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/config.php';

function smartcms_cleanup_remove_dir(string $path): int
{
    if (!is_dir($path)) {
        return 0;
    }

    $deleted = 0;
    $items = scandir($path);
    if ($items === false) {
        return 0;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $target = $path . DIRECTORY_SEPARATOR . $item;
        if (is_dir($target)) {
            $deleted += smartcms_cleanup_remove_dir($target);
            if (@rmdir($target)) {
                $deleted++;
            }
            continue;
        }

        if (is_file($target) && @unlink($target)) {
            $deleted++;
        }
    }

    return $deleted;
}

$cache_root = SMARTCMS_ROOT . '/uploads/thumbnails';
$mode = in_array('--all', $argv ?? [], true) ? 'all' : 'legacy';
$deleted = 0;
$removed_dirs = [];

if (is_dir($cache_root)) {
    $entries = scandir($cache_root);
    if ($entries !== false) {
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $target = $cache_root . DIRECTORY_SEPARATOR . $entry;
            if (!is_dir($target)) {
                continue;
            }

            if ($mode === 'legacy' && $entry === 'v2') {
                continue;
            }

            $deleted += smartcms_cleanup_remove_dir($target);
            if (@rmdir($target)) {
                $deleted++;
            }
            $removed_dirs[] = $entry;
        }
    }
}

$message = $mode === 'all'
    ? '썸네일 캐시 전체 정리 완료'
    : '구버전 썸네일 캐시 정리 완료';

if (PHP_SAPI === 'cli') {
    echo $message . PHP_EOL;
    echo '삭제 항목 수: ' . $deleted . PHP_EOL;
    if ($removed_dirs) {
        echo '삭제한 디렉토리: ' . implode(', ', $removed_dirs) . PHP_EOL;
    }
    exit(0);
}

header('Content-Type: text/plain; charset=utf-8');
echo $message . "\n";
echo '삭제 항목 수: ' . $deleted . "\n";
if ($removed_dirs) {
    echo '삭제한 디렉토리: ' . implode(', ', $removed_dirs) . "\n";
}
