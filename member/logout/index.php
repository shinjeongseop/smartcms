<?php
declare(strict_types=1);

require_once __DIR__ . '/../../common/auth.php';

smartcms_logout();
smartcms_redirect((string)smartcms_config_value('login_url', '/member/login/'));
