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

    $html  = '<nav class="navbar navbar-expand-md bg-body border-bottom sticky-top shadow-sm w-100" aria-label="사이트 메뉴" data-bs-theme="light">';
    $html .= '<div class="container-fluid px-4 px-lg-5 py-2">';

    // 브랜드
    $html .= '<a class="navbar-brand d-inline-flex align-items-center gap-2 text-decoration-none text-primary fw-semibold" href="' . $base('/') . '">';
    $html .= '<span class="badge text-bg-primary rounded-3 p-2"><i class="bi bi-grid-3x3-gap-fill"></i></span>';
    $html .= '<strong>smartcms</strong></a>';

    // 토글 버튼 (모바일)
    $html .= '<button class="navbar-toggler border-0" type="button"'
           . ' data-bs-toggle="collapse" data-bs-target="#scSiteNav"'
           . ' aria-controls="scSiteNav" aria-expanded="false" aria-label="메뉴 열기">'
           . '<span class="navbar-toggler-icon"></span></button>';

    // 내비 링크
    $html .= '<div class="collapse navbar-collapse" id="scSiteNav">';
    $html .= '<ul class="navbar-nav ms-md-auto align-items-md-center gap-md-1">';

    foreach ($items as $key => $item) {
        $cls  = 'nav-link px-3' . ($key === $active ? ' active fw-semibold text-primary' : '');
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

    $html .= '<li class="nav-item ms-md-2">'
           . '<a class="btn btn-primary btn-sm rounded-pill px-3" href="' . $base($ctaHref) . '">'
           . '<i class="bi ' . $ctaIcon . ' me-1"></i>' . $ctaLabel
           . '</a></li>';

    $html .= '</ul></div></div></nav>';

    return $html;
}

/**
 * 사이트 페이지 시작 래퍼
 * <main> + nav
 */
function smartcms_site_header(string $active = '', string $extra_class = ''): string
{
    $cls = trim($extra_class);
    $mainClass = trim('bg-body min-vh-100 ' . $cls);
    return '<main class="' . smartcms_h($mainClass) . '">'
         . smartcms_site_nav($active);
}

/**
 * 사이트 페이지 닫힘 래퍼
 */
function smartcms_site_footer(): string
{
    $year = date('Y');
    return '<footer class="mt-5 border-top bg-body">'
         . '<div class="container-xxl py-5">'
         . '<div class="row row-cols-1 row-cols-sm-2 row-cols-md-5 g-4">'
         . '<div class="col">'
         . '<a class="d-inline-flex align-items-center gap-2 text-decoration-none text-body mb-3" href="' . smartcms_h(smartcms_base_url('/')) . '">'
         . '<span class="badge text-bg-primary rounded-3 p-2"><i class="bi bi-grid-3x3-gap-fill"></i></span>'
         . '<strong>smartcms</strong></a>'
         . '<p class="text-body-secondary small mb-0">경량 커뮤니티 CMS</p>'
         . '</div>'
         . '<div class="col"><h2 class="h6 fw-semibold mb-3">서비스</h2><ul class="list-unstyled d-grid gap-2 small">'
         . '<li><a class="text-decoration-none text-body-secondary" href="' . smartcms_h(smartcms_base_url('/board/')) . '">게시판 목록</a></li>'
         . '<li><a class="text-decoration-none text-body-secondary" href="' . smartcms_h(smartcms_base_url('/install/')) . '">설치 마법사</a></li>'
         . '<li><a class="text-decoration-none text-body-secondary" href="' . smartcms_h(smartcms_base_url('/member/login/')) . '">로그인</a></li>'
         . '</ul></div>'
         . '<div class="col"><h2 class="h6 fw-semibold mb-3">게시판</h2><ul class="list-unstyled d-grid gap-2 small">'
         . '<li><a class="text-decoration-none text-body-secondary" href="' . smartcms_h(smartcms_base_url('/board/?board=notice')) . '">공지사항</a></li>'
         . '<li><a class="text-decoration-none text-body-secondary" href="' . smartcms_h(smartcms_base_url('/board/?board=free')) . '">자유게시판</a></li>'
         . '<li><a class="text-decoration-none text-body-secondary" href="' . smartcms_h(smartcms_base_url('/board/?board=qna')) . '">Q&A</a></li>'
         . '</ul></div>'
         . '<div class="col"><h2 class="h6 fw-semibold mb-3">회원</h2><ul class="list-unstyled d-grid gap-2 small">'
         . '<li><a class="text-decoration-none text-body-secondary" href="' . smartcms_h(smartcms_base_url('/member/register/')) . '">회원가입</a></li>'
         . '<li><a class="text-decoration-none text-body-secondary" href="' . smartcms_h(smartcms_base_url('/member/mypage/')) . '">마이페이지</a></li>'
         . '<li><a class="text-decoration-none text-body-secondary" href="' . smartcms_h(smartcms_base_url('/member/password/')) . '">비밀번호 변경</a></li>'
         . '</ul></div>'
         . '<div class="col"><h2 class="h6 fw-semibold mb-3">관리</h2><ul class="list-unstyled d-grid gap-2 small">'
         . '<li><a class="text-decoration-none text-body-secondary" href="' . smartcms_h(smartcms_base_url('/admin/')) . '">관리자</a></li>'
         . '<li><a class="text-decoration-none text-body-secondary" href="' . smartcms_h(smartcms_base_url('/admin/dashboard/')) . '">대시보드</a></li>'
         . '<li><a class="text-decoration-none text-body-secondary" href="' . smartcms_h(smartcms_base_url('/admin/settings/')) . '">환경 설정</a></li>'
         . '</ul></div>'
         . '</div>'
         . '<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 pt-4 mt-4 border-top small text-body-secondary">'
         . '<span>&copy; ' . smartcms_h($year) . ' smartcms</span>'
         . '<span>Bootstrap footer demo style</span>'
         . '</div>'
         . '</footer>'
         . '</main>';
}
