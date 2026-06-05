<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/security.php';

function smartcms_alert(string $message, string $type = 'info'): string
{
    return '<div class="smartcms-alert smartcms-alert--' . smartcms_h($type) . '">' . smartcms_h($message) . '</div>';
}

function smartcms_button(string $label, string $type = 'button'): string
{
    return '<button class="smartcms-btn" type="' . smartcms_h($type) . '">' . smartcms_h($label) . '</button>';
}
