<?php
declare(strict_types=1);

define('SMARTCMS_ROOT', dirname(__DIR__));

function smartcms_config(): array
{
    static $config = null;
    if ($config !== null) {
        return $config;
    }

    $example = SMARTCMS_ROOT . '/config.example.php';
    $local = SMARTCMS_ROOT . '/config.local.php';
    $base = is_file($example) ? require $example : [];
    $override = is_file($local) ? require $local : [];

    $config = smartcms_array_merge($base, $override);
    return $config;
}

function smartcms_array_merge(array $base, array $override): array
{
    foreach ($override as $key => $value) {
        if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
            $base[$key] = smartcms_array_merge($base[$key], $value);
            continue;
        }
        $base[$key] = $value;
    }

    return $base;
}

function smartcms_config_value(string $key, mixed $default = null): mixed
{
    $config = smartcms_config();
    foreach (explode('.', $key) as $part) {
        if (!is_array($config) || !array_key_exists($part, $config)) {
            return $default;
        }
        $config = $config[$part];
    }

    return $config;
}

function smartcms_table(string $name): string
{
    $prefix = (string)smartcms_config_value('table_prefix', '');
    $safe = preg_replace('/[^a-zA-Z0-9_]/', '', $prefix . $name);
    return $safe !== '' ? $safe : $name;
}

function smartcms_base_url(string $path = ''): string
{
    $base = rtrim((string)smartcms_config_value('base_url', ''), '/');
    if ($base === '') {
        $scheme = 'https';
        $forwarded_proto = strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
        if ($forwarded_proto === 'http' || $forwarded_proto === 'https') {
            $scheme = $forwarded_proto;
        } elseif (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $scheme = 'https';
        } else {
            $scheme = 'http';
        }

        $host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
        $base = $scheme . '://' . $host;
    }

    return $base . '/' . ltrim($path, '/');
}

function smartcms_asset_url(string $path): string
{
    if (preg_match('#^(https?:)?//#', $path) === 1 || str_starts_with($path, 'data:')) {
        return $path;
    }

    return '/' . ltrim($path, '/');
}

function smartcms_session_idle_timeout_seconds(): int
{
    return max(300, (int)smartcms_config_value('session_idle_timeout_seconds', 10800));
}

function smartcms_session_last_activity_key(): string
{
    return 'smartcms_last_activity';
}

function smartcms_h(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
