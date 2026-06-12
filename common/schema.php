<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

function smartcms_create_schema(): void
{
    smartcms_create_users_table();
    smartcms_create_page_permissions_table();
    smartcms_create_board_permissions_table();
    smartcms_create_boards_table();
    smartcms_create_board_posts_table();
    smartcms_create_board_comments_table();
    smartcms_create_board_files_table();
    smartcms_create_board_audit_logs_table();
    smartcms_create_login_logs_table();
    smartcms_create_access_logs_table();
    smartcms_create_site_settings_table();
}

function smartcms_create_users_table(): void
{
    smartcms_db()->exec("CREATE TABLE IF NOT EXISTS " . smartcms_table('users') . " (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(190) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        name VARCHAR(80) NOT NULL,
        nickname VARCHAR(80) DEFAULT NULL,
        company_name VARCHAR(120) DEFAULT NULL,
        avatar_path VARCHAR(255) DEFAULT NULL,
        role ENUM('admin','manager','user') NOT NULL DEFAULT 'user',
        level TINYINT UNSIGNED NOT NULL DEFAULT 2,
        status ENUM('active','pending','blocked','left') NOT NULL DEFAULT 'active',
        last_login_at DATETIME DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_users_email (email),
        INDEX idx_users_role_level (role, level),
        INDEX idx_users_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function smartcms_create_page_permissions_table(): void
{
    smartcms_db()->exec("CREATE TABLE IF NOT EXISTS " . smartcms_table('page_permissions') . " (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        page_key VARCHAR(80) NOT NULL,
        page_path VARCHAR(255) NOT NULL,
        title VARCHAR(120) NOT NULL,
        page_view_level TINYINT UNSIGNED NOT NULL DEFAULT 0,
        page_write_level TINYINT UNSIGNED NOT NULL DEFAULT 8,
        page_manage_level TINYINT UNSIGNED NOT NULL DEFAULT 8,
        allow_guest TINYINT(1) NOT NULL DEFAULT 1,
        status ENUM('active','disabled') NOT NULL DEFAULT 'active',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_page_permissions_key (page_key),
        INDEX idx_page_permissions_path (page_path),
        INDEX idx_page_permissions_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function smartcms_create_board_permissions_table(): void
{
    smartcms_db()->exec("CREATE TABLE IF NOT EXISTS " . smartcms_table('board_permissions') . " (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        board_key VARCHAR(80) NOT NULL,
        board_name VARCHAR(120) NOT NULL,
        board_list_level TINYINT UNSIGNED NOT NULL DEFAULT 0,
        board_view_level TINYINT UNSIGNED NOT NULL DEFAULT 0,
        board_write_level TINYINT UNSIGNED NOT NULL DEFAULT 8,
        board_comment_level TINYINT UNSIGNED NOT NULL DEFAULT 2,
        board_upload_level TINYINT UNSIGNED NOT NULL DEFAULT 8,
        board_manage_level TINYINT UNSIGNED NOT NULL DEFAULT 8,
        allow_guest_list TINYINT(1) NOT NULL DEFAULT 1,
        allow_guest_view TINYINT(1) NOT NULL DEFAULT 1,
        status ENUM('active','disabled') NOT NULL DEFAULT 'active',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_board_permissions_key (board_key),
        INDEX idx_board_permissions_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function smartcms_create_boards_table(): void
{
    smartcms_db()->exec("CREATE TABLE IF NOT EXISTS " . smartcms_table('boards') . " (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        board_key VARCHAR(80) NOT NULL,
        board_name VARCHAR(120) NOT NULL,
        description TEXT DEFAULT NULL,
        skin VARCHAR(40) NOT NULL DEFAULT 'default',
        display_type ENUM('auto','card','list','table') NOT NULL DEFAULT 'auto',
        author_display_mode ENUM('name','nickname','name_nickname') NOT NULL DEFAULT 'name',
        items_per_page TINYINT UNSIGNED NOT NULL DEFAULT 10,
        use_editor TINYINT(1) NOT NULL DEFAULT 1,
        use_comments TINYINT(1) NOT NULL DEFAULT 1,
        use_attachments TINYINT(1) NOT NULL DEFAULT 1,
        status ENUM('active','hidden','disabled') NOT NULL DEFAULT 'active',
        created_by BIGINT UNSIGNED DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_boards_key (board_key),
        INDEX idx_boards_status (status),
        INDEX idx_boards_created_by (created_by)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function smartcms_create_board_posts_table(): void
{
    smartcms_db()->exec("CREATE TABLE IF NOT EXISTS " . smartcms_table('board_posts') . " (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        board_id BIGINT UNSIGNED NOT NULL,
        parent_id BIGINT UNSIGNED DEFAULT NULL,
        category VARCHAR(80) DEFAULT NULL,
        title VARCHAR(255) NOT NULL,
        link_url VARCHAR(500) DEFAULT NULL,
        link_url_1 VARCHAR(500) DEFAULT NULL,
        link_url_2 VARCHAR(500) DEFAULT NULL,
        content MEDIUMTEXT NOT NULL,
        content_mode ENUM('text','editor') NOT NULL DEFAULT 'text',
        excerpt VARCHAR(500) DEFAULT NULL,
        author_id BIGINT UNSIGNED DEFAULT NULL,
        author_name VARCHAR(80) NOT NULL,
        is_notice TINYINT(1) NOT NULL DEFAULT 0,
        is_secret TINYINT(1) NOT NULL DEFAULT 0,
        is_hidden TINYINT(1) NOT NULL DEFAULT 0,
        view_count INT UNSIGNED NOT NULL DEFAULT 0,
        comment_count INT UNSIGNED NOT NULL DEFAULT 0,
        attachment_count INT UNSIGNED NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_board_posts_board_created (board_id, created_at),
        INDEX idx_board_posts_board_notice (board_id, is_notice, created_at),
        INDEX idx_board_posts_author (author_id),
        FULLTEXT KEY ft_board_posts_title_content (title, content)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function smartcms_create_board_comments_table(): void
{
    smartcms_db()->exec("CREATE TABLE IF NOT EXISTS " . smartcms_table('board_comments') . " (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        board_id BIGINT UNSIGNED NOT NULL,
        post_id BIGINT UNSIGNED NOT NULL,
        parent_id BIGINT UNSIGNED DEFAULT NULL,
        author_id BIGINT UNSIGNED DEFAULT NULL,
        author_name VARCHAR(80) NOT NULL,
        content TEXT NOT NULL,
        is_hidden TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_board_comments_post_created (post_id, created_at),
        INDEX idx_board_comments_parent (parent_id),
        INDEX idx_board_comments_author (author_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function smartcms_create_board_files_table(): void
{
    smartcms_db()->exec("CREATE TABLE IF NOT EXISTS " . smartcms_table('board_files') . " (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        board_id BIGINT UNSIGNED NOT NULL,
        post_id BIGINT UNSIGNED DEFAULT NULL,
        comment_id BIGINT UNSIGNED DEFAULT NULL,
        original_name VARCHAR(255) NOT NULL,
        stored_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
        mime_type VARCHAR(120) DEFAULT NULL,
        download_count INT UNSIGNED NOT NULL DEFAULT 0,
        uploaded_by BIGINT UNSIGNED DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_board_files_post (post_id),
        INDEX idx_board_files_comment (comment_id),
        INDEX idx_board_files_uploaded_by (uploaded_by)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function smartcms_create_board_audit_logs_table(): void
{
    smartcms_db()->exec("CREATE TABLE IF NOT EXISTS " . smartcms_table('board_audit_logs') . " (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        board_id BIGINT UNSIGNED DEFAULT NULL,
        post_id BIGINT UNSIGNED DEFAULT NULL,
        user_id BIGINT UNSIGNED DEFAULT NULL,
        action VARCHAR(80) NOT NULL,
        message VARCHAR(500) NOT NULL,
        ip_hash CHAR(64) DEFAULT NULL,
        user_agent VARCHAR(255) DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_board_audit_board_created (board_id, created_at),
        INDEX idx_board_audit_user_created (user_id, created_at),
        INDEX idx_board_audit_action (action)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function smartcms_create_login_logs_table(): void
{
    smartcms_db()->exec("CREATE TABLE IF NOT EXISTS " . smartcms_table('login_logs') . " (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED DEFAULT NULL,
        email VARCHAR(190) NOT NULL,
        ip_hash CHAR(64) DEFAULT NULL,
        user_agent VARCHAR(255) DEFAULT NULL,
        result ENUM('success','fail','blocked') NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_login_logs_user_created (user_id, created_at),
        INDEX idx_login_logs_email_created (email, created_at),
        INDEX idx_login_logs_result_created (result, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function smartcms_create_access_logs_table(): void
{
    smartcms_db()->exec("CREATE TABLE IF NOT EXISTS " . smartcms_table('access_logs') . " (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED DEFAULT NULL,
        session_key VARCHAR(128) DEFAULT NULL,
        access_type ENUM('page_view','admin_view','login_success','login_fail','logout','permission_denied') NOT NULL,
        target_type ENUM('page','admin','board','member','system') NOT NULL DEFAULT 'page',
        target_key VARCHAR(120) DEFAULT NULL,
        request_path VARCHAR(255) NOT NULL,
        method VARCHAR(10) NOT NULL DEFAULT 'GET',
        ip_hash CHAR(64) DEFAULT NULL,
        origin VARCHAR(255) DEFAULT NULL,
        referer VARCHAR(500) DEFAULT NULL,
        user_agent VARCHAR(255) DEFAULT NULL,
        result ENUM('success','fail','denied') NOT NULL DEFAULT 'success',
        status_code SMALLINT UNSIGNED NOT NULL DEFAULT 200,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_access_logs_user_created (user_id, created_at),
        INDEX idx_access_logs_type_created (access_type, created_at),
        INDEX idx_access_logs_target_created (target_type, target_key, created_at),
        INDEX idx_access_logs_ip_created (ip_hash, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function smartcms_create_site_settings_table(): void
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
