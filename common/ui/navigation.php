<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';

/**
 * 사이트 상단 내비게이션 HTML 반환
 */
function smartcms_site_nav(string $active = ''): string
{
    $installed = is_file(smartcms_config_local_path()) && smartcms_install_locked();

    $items = [
        'home' => ['label' => '홈', 'href' => '/', 'icon' => 'bi-house-fill'],
    ];

    if ($installed) {
        $items += [
            'notice' => ['label' => '공지사항', 'href' => '/board/?board=notice', 'icon' => 'bi-megaphone-fill'],
            'free'   => ['label' => '자유게시판', 'href' => '/board/?board=free',   'icon' => 'bi-chat-square-text-fill'],
            'qna'    => ['label' => 'Q&A',        'href' => '/board/?board=qna',    'icon' => 'bi-question-circle-fill'],
        ];
    }

    $base   = static fn(string $href): string => smartcms_h(smartcms_base_url($href));
    $h      = 'smartcms_h';

    $html  = '<nav class="navbar navbar-expand-lg sc-navbar" aria-label="사이트 메뉴">';
    $html .= '<div class="container-fluid px-0">';

    // 브랜드
    $html .= '<a class="navbar-brand sc-brand" href="' . $base('/') . '">';
    $html .= '<span class="sc-brand-icon"><i class="bi bi-grid-3x3-gap-fill"></i></span>';
    $html .= '<strong>smartcms</strong></a>';

    // 토글 버튼 (모바일)
    $html .= '<button class="navbar-toggler border-0" type="button"'
           . ' data-bs-toggle="collapse" data-bs-target="#scSiteNav"'
           . ' aria-controls="scSiteNav" aria-expanded="false" aria-label="메뉴 열기">'
           . '<span class="navbar-toggler-icon"></span></button>';

    // 내비 링크
    $html .= '<div class="collapse navbar-collapse" id="scSiteNav">';
    $html .= '<ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">';

    foreach ($items as $key => $item) {
        $cls  = 'nav-link' . ($key === $active ? ' active' : '');
        $html .= '<li class="nav-item">'
               . '<a class="' . $h($cls) . '" href="' . $base($item['href']) . '">'
               . '<i class="bi ' . $h($item['icon']) . ' me-1"></i>'
               . $h($item['label'])
               . '</a></li>';
    }

    // CTA 버튼
    $ctaHref  = $installed ? '/admin/'    : '/install/';
    $ctaIcon  = $installed ? 'bi-speedometer2' : 'bi-magic';
    $ctaLabel = $installed ? '관리자'      : '설치하기';

    $html .= '<li class="nav-item ms-lg-2">'
           . '<a class="btn btn-primary btn-sm rounded-pill px-3" href="' . $base($ctaHref) . '">'
           . '<i class="bi ' . $ctaIcon . ' me-1"></i>' . $ctaLabel
           . '</a></li>';

    $html .= '</ul></div></div></nav>';

    return $html;
}

/**
 * 사이트 페이지 시작 래퍼
 * <main class="sc-page"> + <div class="sc-container"> + nav
 */
function smartcms_site_header(string $active = '', string $extra_class = ''): string
{
    $cls = trim('sc-page ' . $extra_class);
    return '<main class="' . smartcms_h($cls) . '">'
         . '<div class="sc-container sc-content-wrap">'
         . smartcms_site_nav($active);
}

/**
 * 사이트 페이지 닫힘 래퍼
 */
function smartcms_site_footer(): string
{
    $year = date('Y');
    return '<footer class="sc-footer">'
         . '<div><strong>smartcms</strong><span>경량 커뮤니티 CMS</span></div>'
         . '<small>&copy; ' . smartcms_h($year) . ' smartcms</small>'
         . '</footer>'
         . '</div></main>'; // .sc-container + .sc-page 닫기
}
