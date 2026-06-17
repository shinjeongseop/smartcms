<?php
declare(strict_types=1);

function smartcms_version(): string
{
    $version = trim((string)smartcms_config_value('smartcms.version', '2.2.0'));
    return $version !== '' ? $version : '2.2.0';
}

function smartcms_version_tag(): string
{
    return 'v' . smartcms_version();
}
