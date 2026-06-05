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

function smartcms_config_local_path(): string
{
    return SMARTCMS_ROOT . '/config.local.php';
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
    return $base . '/' . ltrim($path, '/');
}

function smartcms_h(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function smartcms_install_locked(): bool
{
    return is_file(SMARTCMS_ROOT . '/install.lock');
}

function smartcms_write_local_config(array $settings): bool
{
    $config = [
        'project_key' => trim((string)($settings['project_key'] ?? 'smartcms')),
        'base_url' => rtrim(trim((string)($settings['base_url'] ?? '')), '/'),
        'table_prefix' => preg_replace('/[^a-zA-Z0-9_]/', '', (string)($settings['table_prefix'] ?? 'sc_')) ?: 'sc_',
        'db' => [
            'host' => trim((string)($settings['db']['host'] ?? 'localhost')),
            'name' => trim((string)($settings['db']['name'] ?? '')),
            'user' => trim((string)($settings['db']['user'] ?? '')),
            'pass' => (string)($settings['db']['pass'] ?? ''),
            'charset' => trim((string)($settings['db']['charset'] ?? 'utf8mb4')),
        ],
    ];

    $contents = "<?php\nreturn " . var_export($config, true) . ";\n";
    return file_put_contents(smartcms_config_local_path(), $contents, LOCK_EX) !== false;
}
