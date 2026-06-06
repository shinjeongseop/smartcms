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
    return '<main class="container min-vh-100 d-flex align-items-center py-5">'
         . '<div class="row justify-content-center w-100">'
         . '<div class="col-12 col-md-10 col-lg-7 col-xl-6">';
}

/**
 * 인증 페이지 닫힘
 */
function smartcms_auth_footer(): string
{
    return '</div></div></main>';
}

/* ─────────────────────────────────────────
   3. 공통 UI 조각
───────────────────────────────────────── */

/**
 * 섹션 타이틀 + 우측 액션 조합 헤더
 */
function smartcms_section_head(string $title, string $action_html = ''): string
{
    $html = '<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">';
    $html .= '<h2 class="h5 mb-0 fw-semibold text-body">' . smartcms_h($title) . '</h2>';
    if ($action_html !== '') {
        $html .= $action_html;
    }
    $html .= '</div>';
    return $html;
}
