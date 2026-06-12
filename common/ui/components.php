<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/security.php';
require_once dirname(__DIR__) . '/auth.php';
require_once dirname(__DIR__) . '/board.php';

/* ─────────────────────────────────────────
   1. 데이터 인터페이스
───────────────────────────────────────── */

if (!function_exists('smartcms_site_nav_items')) {
    function smartcms_site_nav_items(): array
    {
        $items = [
            'home' => ['label' => '홈', 'href' => '/', 'icon' => 'bi-house-fill'],
            'boards' => ['label' => '게시판', 'href' => '/board/', 'icon' => 'bi-grid-3x3-gap-fill'],
        ];

        try {
            foreach (smartcms_board_list() as $board) {
                if ((string)($board['status'] ?? '') !== 'active') {
                    continue;
                }

                $board_key = (string)($board['board_key'] ?? '');
                if ($board_key === '') {
                    continue;
                }

                $skin_meta = smartcms_board_skin_meta($board);
                $items['board:' . $board_key] = [
                    'label' => (string)($board['board_name'] ?? $board_key),
                    'href' => '/board/?board=' . rawurlencode($board_key),
                    'icon' => (string)($skin_meta['icon'] ?? 'bi-grid-3x3-gap-fill'),
                ];
            }
        } catch (Throwable $e) {
            $items += [
                'notice' => ['label' => '공지사항', 'href' => '/board/?board=notice', 'icon' => 'bi-megaphone-fill'],
                'free' => ['label' => '자유게시판', 'href' => '/board/?board=free', 'icon' => 'bi-chat-square-text-fill'],
                'qna' => ['label' => '질문과 답변', 'href' => '/board/?board=qna', 'icon' => 'bi-question-circle-fill'],
            ];
        }

        return $items;
    }
}

if (!function_exists('smartcms_admin_nav_items')) {
    function smartcms_admin_nav_items(): array
    {
        return [
            'dashboard' => ['label' => '대시보드', 'href' => '/admin/dashboard/', 'icon' => 'bi-speedometer2'],
            'users'     => ['label' => '회원 관리', 'href' => '/admin/users/', 'icon' => 'bi-people-fill'],
            'boards'    => ['label' => '게시판 관리', 'href' => '/admin/boards/', 'icon' => 'bi-layout-text-window'],
            'pages'     => ['label' => '페이지 권한', 'href' => '/admin/pages/', 'icon' => 'bi-shield-lock-fill'],
            'logs'      => ['label' => '접속 로그', 'href' => '/admin/logs/', 'icon' => 'bi-activity'],
            'database'  => ['label' => 'DB 관리', 'href' => '/admin/database/', 'icon' => 'bi-database-fill'],
            'settings'  => ['label' => '환경 설정', 'href' => '/admin/settings/', 'icon' => 'bi-gear-fill'],
        ];
    }
}

if (!function_exists('smartcms_user_avatar_markup')) {
    function smartcms_user_avatar_markup(?array $user, string $size_class = 'sc-avatar-72', string $fallback_text_class = 'fw-bold'): string
    {
        $size_class = trim($size_class);
        $fallback_text_class = trim($fallback_text_class);
        $avatar_size = preg_match('/(\d+)/', $size_class, $matches) === 1 ? (int)$matches[1] : 72;
        $avatar_url = smartcms_user_avatar_url($user);

        if ($avatar_url !== null) {
            return '<img src="' . smartcms_h($avatar_url) . '" alt="" width="' . $avatar_size . '" height="' . $avatar_size . '" class="rounded-circle object-fit-cover shadow-sm ' . smartcms_h($size_class) . '">';
        }

        $display_name = smartcms_user_display_name($user);
        $label = trim(mb_substr($display_name, 0, 1));
        if ($label === '') {
            $label = '?';
        }

        return '<span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary text-white shadow-sm ' . smartcms_h($size_class) . ' ' . smartcms_h($fallback_text_class) . '">' . smartcms_h($label) . '</span>';
    }
}
