<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';

/**
 * HTML <head> 출력
 * $page 옵션:
 *   title       string  페이지 제목
 *   body_class  string  <body> 추가 클래스
 *   stylesheets array   추가 CSS URL 목록
 */
function smartcms_render_head(array $page = []): void
{
    $title      = (string)($page['title'] ?? 'smartcms');
    $body_class = (string)($page['body_class'] ?? '');
    $css_url    = (string)smartcms_config_value('theme.css_url', '/common/css/common.css');
    ?>
<!doctype html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= smartcms_h($title) ?> — smartcms</title>
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Editorial sans + Korean fallback + code mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&family=Noto+Sans+KR:wght@400;500;600&display=swap">
  <!-- smartcms 공통 스타일 -->
  <link rel="stylesheet" href="<?= smartcms_h(smartcms_asset_url($css_url)) ?>">
  <?php foreach (($page['stylesheets'] ?? []) as $stylesheet): ?>
    <link rel="stylesheet" href="<?= smartcms_h(smartcms_asset_url((string)$stylesheet)) ?>">
  <?php endforeach; ?>
</head>
<body class="<?= smartcms_h($body_class) ?>">
    <?php
}

/**
 * Bootstrap JS + 추가 스크립트 + </body></html>
 */
function smartcms_render_foot(array $page = []): void
{
    echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>' . PHP_EOL;
    foreach (($page['scripts'] ?? []) as $script) {
        echo '<script src="' . smartcms_h(smartcms_asset_url((string)$script)) . '"></script>' . PHP_EOL;
    }
    echo '</body></html>';
}
