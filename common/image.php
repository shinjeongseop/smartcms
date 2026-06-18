<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/*
이름 : smartcms_image_load_resource
용도 : 이미지파일(gif, jpg, png 만 지원)로부터 이미지 리소스를 생성한다.
성공시 리턴값 : [image resource, width, height, type, attr]
실패시 리턴값 : 빈 배열
*/
function smartcms_image_load_resource(string $path_file): array
{
    if (!is_file($path_file)) {
        $GLOBALS['errormsg'] = $path_file . '은 파일이 아닙니다.';
        return [];
    }

    $size = @getimagesize($path_file);
    if (empty($size[2])) {
        $GLOBALS['errormsg'] = $path_file . '은 이미지 파일이 아닙니다.';
        return [];
    }

    if ($size[2] !== 1 && $size[2] !== 2 && $size[2] !== 3) {
        $GLOBALS['errormsg'] = $path_file . '은 gif 나 jpg, png 파일이 아닙니다.';
        return [];
    }

    switch ($size[2]) {
        case 1:
            $im = @imagecreatefromgif($path_file);
            break;
        case 2:
            $im = @imagecreatefromjpeg($path_file);
            break;
        case 3:
            $im = @imagecreatefrompng($path_file);
            break;
        default:
            $im = false;
    }

    if ($im === false) {
        $GLOBALS['errormsg'] = $path_file . ' 에서 이미지 리소스를 가져오는 것에 실패하였습니다.';
        return [];
    }

    $return = $size;
    $return[0] = $im;
    $return[1] = (int)$size[0];
    $return[2] = (int)$size[1];
    $return[3] = (int)$size[2];
    $return[4] = (string)$size[3];

    return $return;
}

/*
이름 : smartcms_image_check_savable
용도 : 인자로 받은 파일 경로로 저장가능한지 여부 확인
*/
function smartcms_image_check_savable(string $path_save_file): bool
{
    $path_save_dir = dirname($path_save_file);
    if (!is_dir($path_save_dir)) {
        $GLOBALS['errormsg'] = $path_save_dir . '은 디렉토리가 아닙니다.';
        return false;
    }

    if (!is_writable($path_save_dir)) {
        $GLOBALS['errormsg'] = $path_save_dir . '에 이미지를 저장할 권한이 없습니다.';
        return false;
    }

    if (is_dir($path_save_file)) {
        $GLOBALS['errormsg'] = $path_save_file . '은 이미 같은 이름의 디렉토리가 존재합니다.';
        return false;
    }

    return true;
}

/*
이름 : smartcms_image_save_resource
용도 : image resource 를 가지고 파일로 저장
*/
function smartcms_image_save_resource($im, string $path_save_file, int $quality = 85, int $save_force = 0): bool
{
    if (!smartcms_image_check_savable($path_save_file)) {
        return false;
    }

    if (is_file($path_save_file)) {
        if ($save_force === 1) {
            return true;
        }

        if ($save_force === 2) {
            if (@unlink($path_save_file) === false) {
                $GLOBALS['errormsg'] = '기존에 존재하던 ' . $path_save_file . '의 삭제에 실패하였습니다.';
                return false;
            }
        } else {
            $GLOBALS['errormsg'] = $path_save_file . '은 이미 같은 이름의 파일이 존재합니다.';
            return false;
        }
    }

    $extension = strtolower(substr($path_save_file, strrpos($path_save_file, '.') + 1));

    switch ($extension) {
        case 'gif':
            $result_save = @imagegif($im, $path_save_file);
            break;
        case 'jpg':
        case 'jpeg':
            $result_save = @imagejpeg($im, $path_save_file, $quality);
            break;
        case 'webp':
            $result_save = function_exists('imagewebp') ? @imagewebp($im, $path_save_file, $quality) : false;
            break;
        default:
            $result_save = @imagepng($im, $path_save_file);
    }

    if ($result_save === false) {
        $GLOBALS['errormsg'] = $path_save_file . '의 저장에 실패하였습니다.';
        return false;
    }

    return true;
}

/*
이름 : smartcms_image_get_size_by_rule
용도 : 큰이미지의 너비와 높이를 가지고 정비율의 작은 이미지 너비나 높이를 구함
*/
function smartcms_image_get_size_by_rule(int $src_w, int $src_h, int $dst_size, string $rule = 'width'): int|false
{
    if ($src_w < 1 || $src_h < 1) {
        $GLOBALS['errormsg'] = "원본의 너비와 높이가 0보다 큰 정수가 아닙니다. ($src_w, $src_h)";
        return false;
    }

    if ($dst_size < 1) {
        $GLOBALS['errormsg'] = "리사이즈될 사이즈가 0보다 큰 정수가 아닙니다. ($dst_size)";
        return false;
    }

    if ($rule !== 'height') {
        return (int)ceil($dst_size / $src_w * $src_h);
    }

    return (int)ceil($dst_size / $src_h * $src_w);
}

/*
이름 : smartcms_image_resize_file
용도 : 이미지 파일을 비율 유지로 축소하여 다른 파일로 저장
*/
function smartcms_image_resize_file(string $source_path, string $target_path, int $max_width = 1024, int $max_height = 1024, int $quality = 85): bool
{
    $image = smartcms_image_load_resource($source_path);
    if (!$image) {
        return false;
    }

    $src_w = (int)$image[1];
    $src_h = (int)$image[2];
    $src_type = (int)$image[3];

    if ($src_w <= $max_width && $src_h <= $max_height) {
        if ($source_path === $target_path) {
            return true;
        }

        return copy($source_path, $target_path);
    }

    $ratio = min($max_width / $src_w, $max_height / $src_h);
    $dst_w = max(1, (int)round($src_w * $ratio));
    $dst_h = max(1, (int)round($src_h * $ratio));

    $dst = imagecreatetruecolor($dst_w, $dst_h);
    if ($dst === false) {
        return false;
    }

    if ($src_type === 3) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefill($dst, 0, 0, $transparent);
    }

    imagecopyresampled($dst, $image[0], 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
    $saved = smartcms_image_save_resource($dst, $target_path, $quality, 2);
    imagedestroy($image[0]);
    imagedestroy($dst);

    return $saved;
}

/*
이름 : smartcms_image_resize_file_to_width
용도 : 이미지 파일을 비율 유지로 지정된 최대 너비에 맞춰 축소하여 다른 파일로 저장
*/
function smartcms_image_resize_file_to_width(string $source_path, string $target_path, int $max_width = 1024, int $quality = 85): bool
{
    $image = smartcms_image_load_resource($source_path);
    if (!$image) {
        return false;
    }

    $src_w = (int)$image[1];
    $src_h = (int)$image[2];
    $src_type = (int)$image[3];

    if ($src_w <= $max_width) {
        if ($source_path === $target_path) {
            return true;
        }

        return copy($source_path, $target_path);
    }

    $ratio = $max_width / $src_w;
    $dst_w = max(1, (int)round($src_w * $ratio));
    $dst_h = max(1, (int)round($src_h * $ratio));

    $dst = imagecreatetruecolor($dst_w, $dst_h);
    if ($dst === false) {
        imagedestroy($image[0]);
        return false;
    }

    if ($src_type === 3) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefill($dst, 0, 0, $transparent);
    }

    imagecopyresampled($dst, $image[0], 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
    $saved = smartcms_image_save_resource($dst, $target_path, $quality, 2);
    imagedestroy($image[0]);
    imagedestroy($dst);

    return $saved;
}

function smartcms_image_thumbnail_cache_path(string $source_path, int $max_width, int $max_height): string
{
    $real = realpath($source_path) ?: $source_path;
    $relative = str_starts_with($real, SMARTCMS_ROOT) ? ltrim(substr($real, strlen(SMARTCMS_ROOT)), '/\\') : basename($real);
    $cache_version = 'v2';
    $dir = SMARTCMS_ROOT . '/uploads/thumbnails/' . $cache_version . '/' . $max_width . 'x' . $max_height;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $name = pathinfo($relative, PATHINFO_FILENAME);
    $ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
    if (!in_array($ext, ['gif', 'jpg', 'jpeg', 'png', 'webp'], true)) {
        $ext = 'jpg';
    }

    return $dir . '/' . substr(sha1($relative . '|' . $max_width . 'x' . $max_height), 0, 16) . '_' . $name . '.' . $ext;
}

function smartcms_image_thumbnail_url_from_path(string $source_path, int $max_width = 480, int $max_height = 360, int $quality = 85): ?string
{
    if (!is_file($source_path)) {
        return null;
    }

    $thumb_path = smartcms_image_thumbnail_cache_path($source_path, $max_width, $max_height);
    if (!is_file($thumb_path) || filemtime($thumb_path) < filemtime($source_path)) {
        if (!smartcms_image_resize_file($source_path, $thumb_path, $max_width, $max_height, $quality)) {
            return null;
        }
    }

    $relative = ltrim(str_replace('\\', '/', str_replace(SMARTCMS_ROOT, '', $thumb_path)), '/');
    return smartcms_asset_url('/' . $relative);
}

function smartcms_image_thumbnail_url_from_relative(string $relative_path, int $max_width = 480, int $max_height = 360, int $quality = 85): ?string
{
    $source_path = SMARTCMS_ROOT . '/' . ltrim(str_replace('\\', '/', $relative_path), '/');
    return smartcms_image_thumbnail_url_from_path($source_path, $max_width, $max_height, $quality);
}

function smartcms_image_delete_thumbnail_cache_for_source(string $source_path, array $sizes = [[480, 270], [640, 360], [760, 520], [900, 506]]): int
{
    $deleted = 0;
    foreach ($sizes as $size) {
        $max_width = (int)($size[0] ?? 0);
        $max_height = (int)($size[1] ?? 0);
        if ($max_width < 1 || $max_height < 1) {
            continue;
        }

        $thumb_path = smartcms_image_thumbnail_cache_path($source_path, $max_width, $max_height);
        if (is_file($thumb_path) && @unlink($thumb_path)) {
            $deleted++;
        }
    }

    return $deleted;
}

function smartcms_image_source_path_from_url(string $source_url): ?string
{
    $source_url = trim($source_url);
    if ($source_url === '') {
        return null;
    }

    $path = (string)(parse_url($source_url, PHP_URL_PATH) ?: $source_url);
    $path = ltrim(str_replace('\\', '/', $path), '/');
    if ($path === '') {
        return null;
    }

    $absolute = SMARTCMS_ROOT . '/' . $path;
    $real = realpath($absolute);
    if ($real !== false && is_file($real)) {
        return $real;
    }

    return is_file($absolute) ? $absolute : null;
}

function smartcms_image_extract_sources_from_html(string $html): array
{
    $html = trim($html);
    if ($html === '') {
        return [];
    }

    $sources = [];
    if (class_exists(DOMDocument::class)) {
        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="utf-8"?><div id="smartcms-image-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementById('smartcms-image-root');
        if ($root instanceof DOMElement) {
            foreach ($root->getElementsByTagName('img') as $img) {
                if ($img->hasAttribute('src')) {
                    $sources[] = trim((string)$img->getAttribute('src'));
                }
            }
        }
    } elseif (preg_match_all('#<img[^>]+src=[\"\\\']([^\"\\\']+)[\"\\\']#i', $html, $matches)) {
        $sources = array_map('trim', $matches[1]);
    }

    return array_values(array_filter(array_unique($sources), static fn(string $src): bool => $src !== ''));
}

function smartcms_image_delete_thumbnail_cache_from_html(string $html, array $sizes = [[480, 270], [640, 360], [760, 520], [900, 506]]): int
{
    $deleted = 0;
    foreach (smartcms_image_extract_sources_from_html($html) as $source_url) {
        $source_path = smartcms_image_source_path_from_url($source_url);
        if ($source_path !== null) {
            $deleted += smartcms_image_delete_thumbnail_cache_for_source($source_path, $sizes);
        }
    }

    return $deleted;
}

function smartcms_image_cleanup_thumbnail_cache(string $mode = 'legacy'): array
{
    $cache_root = SMARTCMS_ROOT . '/uploads/thumbnails';
    $mode = $mode === 'all' ? 'all' : 'legacy';
    $deleted = 0;
    $removed_dirs = [];

    if (!is_dir($cache_root)) {
        return ['deleted' => 0, 'removed_dirs' => []];
    }

    $remove_dir = static function (string $path) use (&$remove_dir, &$deleted): bool {
        $items = scandir($path);
        if ($items === false) {
            return false;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $current = $path . DIRECTORY_SEPARATOR . $item;
            if (is_dir($current)) {
                $remove_dir($current);
                if (@rmdir($current)) {
                    $deleted++;
                }
                continue;
            }

            if (is_file($current) && @unlink($current)) {
                $deleted++;
            }
        }

        return true;
    };

    $entries = scandir($cache_root);
    if ($entries === false) {
        return ['deleted' => 0, 'removed_dirs' => []];
    }

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

        $remove_dir($target);
        if (@rmdir($target)) {
            $deleted++;
        }
        $removed_dirs[] = $entry;
    }

    return ['deleted' => $deleted, 'removed_dirs' => $removed_dirs];
}
