<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

function smartcms_default_settings(): array
{
    return [
        'site_name' => 'smartcms',
        'allow_registration' => '1',
        'default_member_level' => (string)smartcms_config_value('default_member_level', 2),
        'admin_level' => (string)smartcms_config_value('admin_level', 8),
        'upload_max_mb' => '10',
        'author_display_mode' => 'name',
    ];
}

function smartcms_settings_all(): array
{
    static $settings = null;
    if ($settings !== null) {
        return $settings;
    }

    $settings = smartcms_default_settings();
    try {
        $stmt = smartcms_db()->query("SELECT setting_key, setting_value FROM " . smartcms_table('site_settings'));
        foreach ($stmt->fetchAll() as $row) {
            $settings[(string)$row['setting_key']] = (string)$row['setting_value'];
        }
    } catch (Throwable) {
        // Missing settings table should not break public pages before installation.
    }

    return $settings;
}

function smartcms_ensure_settings_table(): void
{
    smartcms_db()->exec("CREATE TABLE IF NOT EXISTS " . smartcms_table('site_settings') . " (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(120) NOT NULL,
        setting_value TEXT NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_site_settings_key (setting_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function smartcms_setting(string $key, mixed $default = null): mixed
{
    $settings = smartcms_settings_all();
    return array_key_exists($key, $settings) ? $settings[$key] : $default;
}

function smartcms_setting_int(string $key, int $default): int
{
    return (int)smartcms_setting($key, (string)$default);
}

function smartcms_setting_bool(string $key, bool $default): bool
{
    return (string)smartcms_setting($key, $default ? '1' : '0') === '1';
}

function smartcms_save_settings(array $values): void
{
    smartcms_ensure_settings_table();

    foreach ($values as $key => $value) {
        smartcms_execute(
            "INSERT INTO " . smartcms_table('site_settings') . " (setting_key, setting_value)
             VALUES (:setting_key, :setting_value)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
            [
                'setting_key' => $key,
                'setting_value' => (string)$value,
            ]
        );
    }
}
