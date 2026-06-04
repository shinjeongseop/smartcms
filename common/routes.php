<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function smartcms_route(string $path = ''): string
{
    return smartcms_base_url($path);
}

function smartcms_redirect(string $path): void
{
    header('Location: ' . smartcms_route($path));
    exit;
}
