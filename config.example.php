<?php
return [
    'project_key' => 'smartcms',
    'base_url' => '',
    'table_prefix' => '',
    'session_name' => 'smartcms_session',
    'cookie_path' => '/',
    'default_member_level' => 2,
    'admin_level' => 8,
    'super_admin_level' => 10,
    'login_url' => '/member/login/',
    'admin_login_url' => '/admin/login/',
    'admin_home_url' => '/admin/users/',
    'db' => [
        'host' => 'localhost',
        'name' => '',
        'user' => '',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'theme' => [
        'head_file' => null,
        'foot_file' => null,
        'css_url' => '/common/css/common.css',
    ],
];
