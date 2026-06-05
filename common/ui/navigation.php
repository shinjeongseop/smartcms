<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';

function smartcms_site_nav(string $active = ''): string
{
    $installed = is_file(smartcms_config_local_path()) && smartcms_install_locked();
    $items = ['home' => ['label' => '홈', 'href' => '/', 'icon' => 'bi-house']];

    if ($installed) {
        $items += [
            'notice' => ['label' => '공지사항', 'href' => '/board/?board=notice', 'icon' => 'bi-megaphone'],
            'free' => ['label' => '자유게시판', 'href' => '/board/?board=free', 'icon' => 'bi-chat-square-text'],
            'qna' => ['label' => 'Q&A', 'href' => '/board/?board=qna', 'icon' => 'bi-question-circle'],
        ];
    }

    $html = '<nav class="navbar navbar-expand-lg smartcms-site-navbar" aria-label="사이트 메뉴">';
    $html .= '<div class="container-fluid px-0">';
    $html .= '<a class="navbar-brand smartcms-site-brand" href="' . smartcms_h(smartcms_base_url('/')) . '">';
    $html .= '<span class="smartcms-site-brand-mark"><i class="bi bi-grid-3x3-gap-fill"></i></span>';
    $html .= '<strong>smartcms</strong></a>';
    $html .= '<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#smartcmsSiteNav" aria-controls="smartcmsSiteNav" aria-expanded="false" aria-label="메뉴 열기">';
    $html .= '<span class="navbar-toggler-icon"></span></button>';
    $html .= '<div class="collapse navbar-collapse" id="smartcmsSiteNav">';
    $html .= '<ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">';

    foreach ($items as $key => $item) {
        $class = $key === $active ? 'nav-link active' : 'nav-link';
        $html .= '<li class="nav-item"><a class="' . smartcms_h($class) . '" href="' . smartcms_h(smartcms_base_url($item['href'])) . '">';
        $html .= '<i class="bi ' . smartcms_h($item['icon']) . ' me-1"></i>' . smartcms_h($item['label']) . '</a></li>';
    }

    $ctaHref = $installed ? '/admin/' : '/install/';
    $ctaIcon = $installed ? 'bi-speedometer2' : 'bi-magic';
    $ctaLabel = $installed ? '관리자' : '설치하기';
    $html .= '<li class="nav-item ms-lg-2"><a class="btn btn-primary btn-sm rounded-pill px-3" href="' . smartcms_h(smartcms_base_url($ctaHref)) . '">';
    $html .= '<i class="bi ' . $ctaIcon . ' me-1"></i>' . $ctaLabel . '</a></li>';
    $html .= '</ul></div></div></nav>';

    return $html;
}
