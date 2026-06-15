<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function smartcms_route(string $path = ''): string
{
    if (preg_match('#^(https?:)?//#', $path) === 1 || str_starts_with($path, 'data:')) {
        return $path;
    }

    return smartcms_base_url($path);
}

function smartcms_redirect(string $path): void
{
    header('Location: ' . smartcms_route($path));
    exit;
}
