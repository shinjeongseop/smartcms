<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/security.php';
require_once dirname(__DIR__) . '/auth.php';

/* ─────────────────────────────────────────
   1. 알림 / 버튼
───────────────────────────────────────── */

/**
 * 알림 박스 HTML 반환
 * $type: info | success | error | warning
 */
function smartcms_alert(string $message, string $type = 'info'): string
{
    $classes = [
        'info' => 'alert-info',
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
    ];
    $class = $classes[$type] ?? 'alert-info';

    return '<div class="alert ' . $class . ' d-flex align-items-start gap-2" role="alert">'
         . '<i class="bi bi-info-circle-fill mt-1"></i>'
         . '<div>' . smartcms_h($message) . '</div>'
         . '</div>';
}

/**
 * 기본 제출 버튼
 */
function smartcms_button(string $label, string $type = 'button', string $extra_class = ''): string
{
    $cls = trim('btn btn-primary px-4 ' . $extra_class);
    return '<button class="' . smartcms_h($cls) . '" type="' . smartcms_h($type) . '">'
         . smartcms_h($label)
         . '</button>';
}

/* ─────────────────────────────────────────
   2. 공통 UI 조각
───────────────────────────────────────── */

/**
 * 섹션 타이틀 + 우측 액션 조합 헤더
 */
function smartcms_section_head(string $title, string $action_html = ''): string
{
    return '<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">'
         . '<h2 class="h5 mb-0 fw-semibold text-body">' . smartcms_h($title) . '</h2>'
         . ($action_html !== '' ? $action_html : '')
         . '</div>';
}

/**
 * 사이드바 카드
 */
function smartcms_sidebar_card(string $title, string $body_html, string $meta_html = '', string $extra_class = ''): string
{
    $cls = trim('card border-0 shadow-sm ' . $extra_class);
    $html = '<section class="' . smartcms_h($cls) . '">';
    $html .= '<div class="card-body p-4">';
    if ($title !== '') {
        $html .= '<p class="text-uppercase small fw-semibold text-primary mb-2">' . smartcms_h($title) . '</p>';
    }
    $html .= $body_html;
    if ($meta_html !== '') {
        $html .= '<div class="mt-3 pt-3 border-top text-body-secondary small">' . $meta_html . '</div>';
    }
    $html .= '</div></section>';
    return $html;
}

/* ─────────────────────────────────────────
   3. 사이트 레이아웃 헬퍼 (Header / Footer)
───────────────────────────────────────── */

if (!function_exists('smartcms_site_nav_items')) {
    function smartcms_site_nav_items(): array
    {
        return [
            'home' => ['label' => '홈', 'href' => '/', 'icon' => 'bi-house-fill'],
            'boards' => ['label' => '게시판', 'href' => '/board/', 'icon' => 'bi-grid-3x3-gap-fill'],
            'notice' => ['label' => '공지사항', 'href' => '/board/?board=notice', 'icon' => 'bi-megaphone-fill'],
            'free' => ['label' => '자유게시판', 'href' => '/board/?board=free', 'icon' => 'bi-chat-square-text-fill'],
            'qna' => ['label' => '질문과 답변', 'href' => '/board/?board=qna', 'icon' => 'bi-question-circle-fill'],
        ];
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
