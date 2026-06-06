<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/security.php';

/* ─────────────────────────────────────────
   1. 알림 / 버튼
───────────────────────────────────────── */

/**
 * 알림 박스 HTML 반환
 * $type: info | success | error | warning
 */
function smartcms_alert(string $message, string $type = 'info'): string
{
    $icons = [
        'info'    => 'bi-info-circle-fill',
        'success' => 'bi-check-circle-fill',
        'error'   => 'bi-exclamation-triangle-fill',
        'warning' => 'bi-exclamation-circle-fill',
    ];
    $icon = $icons[$type] ?? 'bi-info-circle-fill';

    return '<div class="sc-alert sc-alert--' . smartcms_h($type) . '">'
         . '<i class="bi ' . $icon . '"></i>'
         . '<span>' . smartcms_h($message) . '</span>'
         . '</div>';
}

/**
 * 기본 제출 버튼
 */
function smartcms_button(string $label, string $type = 'button', string $extra_class = ''): string
{
    $cls = trim('btn btn-primary rounded-pill px-4 ' . $extra_class);
    return '<button class="' . smartcms_h($cls) . '" type="' . smartcms_h($type) . '">'
         . smartcms_h($label)
         . '</button>';
}

/* ─────────────────────────────────────────
   2. 인증 페이지 래퍼
───────────────────────────────────────── */

/**
 * 로그인/회원가입 등 인증 페이지 시작
 */
function smartcms_auth_header(string $active = ''): string
{
    return '<div class="sc-auth-wrap">'
         . '<div class="sc-auth-box">';
}

/**
 * 인증 페이지 닫힘
 */
function smartcms_auth_footer(): string
{
    return '</div></div>';
}

/* ─────────────────────────────────────────
   3. 공통 UI 조각
───────────────────────────────────────── */

/**
 * 섹션 타이틀 + 우측 액션 조합 헤더
 */
function smartcms_section_head(string $title, string $action_html = ''): string
{
    $html = '<div class="sc-section-head">';
    $html .= '<h2 class="sc-section-title">' . smartcms_h($title) . '</h2>';
    if ($action_html !== '') {
        $html .= $action_html;
    }
    $html .= '</div>';
    return $html;
}
