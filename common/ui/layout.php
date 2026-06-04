<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';

function smartcms_render_head(array $page = []): void
{
    $title = (string)($page['title'] ?? 'smartcms');
    $body_class = (string)($page['body_class'] ?? '');
    $css_url = (string)smartcms_config_value('theme.css_url', '/common/css/common.css');
    ?>
<!doctype html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= smartcms_h($title) ?></title>
  <link rel="stylesheet" href="<?= smartcms_h(smartcms_base_url($css_url)) ?>">
  <?php foreach (($page['stylesheets'] ?? []) as $stylesheet): ?>
    <link rel="stylesheet" href="<?= smartcms_h((string)$stylesheet) ?>">
  <?php endforeach; ?>
</head>
<body class="<?= smartcms_h($body_class) ?>">
    <?php
}

function smartcms_render_foot(array $page = []): void
{
    foreach (($page['scripts'] ?? []) as $script) {
        echo '<script src="' . smartcms_h((string)$script) . '"></script>' . PHP_EOL;
    }
    echo '</body></html>';
}
