<?php
declare(strict_types=1);

require_once __DIR__ . '/common/config.php';

if (!function_exists('smartcms_site_footer')) {
    function smartcms_site_footer(): string
    {
        $year = date('Y');

        $html  = '<footer class="mt-auto bg-white border-top">';
        $html .= '<div class="container-xxl py-5">';
        $html .= '<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">';
        $html .= '<div class="col">';
        $html .= '<a class="navbar-brand d-inline-flex align-items-center gap-2 fw-bold text-primary text-decoration-none mb-3" href="' . smartcms_h(smartcms_base_url('/')) . '">';
        $html .= '<span class="badge text-bg-primary rounded-circle p-2 lh-1"><i class="bi bi-n-square-fill"></i></span>';
        $html .= '<span>smartcms</span>';
        $html .= '</a>';
        $html .= '<p class="text-body-secondary small mb-0">가볍고 단단한 커뮤니티 CMS</p>';
        $html .= '</div>';
        $html .= '<div class="col"><h2 class="h6 fw-semibold mb-3">서비스</h2><ul class="list-unstyled d-grid gap-2 small">';
        $html .= '<li><a class="text-decoration-none text-body-secondary" href="' . smartcms_h(smartcms_base_url('/board/')) . '">게시판 목록</a></li>';
        $html .= '<li><a class="text-decoration-none text-body-secondary" href="' . smartcms_h(smartcms_base_url('/member/login/')) . '">로그인</a></li>';
        $html .= '<li><a class="text-decoration-none text-body-secondary" href="' . smartcms_h(smartcms_base_url('/member/register/')) . '">회원가입</a></li>';
        $html .= '</ul></div>';
        $html .= '<div class="col"><h2 class="h6 fw-semibold mb-3">게시판</h2><ul class="list-unstyled d-grid gap-2 small">';
        $html .= '<li><a class="text-decoration-none text-body-secondary" href="' . smartcms_h(smartcms_base_url('/board/?board=notice')) . '">공지사항</a></li>';
        $html .= '<li><a class="text-decoration-none text-body-secondary" href="' . smartcms_h(smartcms_base_url('/board/?board=free')) . '">자유게시판</a></li>';
        $html .= '<li><a class="text-decoration-none text-body-secondary" href="' . smartcms_h(smartcms_base_url('/board/?board=qna')) . '">Q&A</a></li>';
        $html .= '</ul></div>';
        $html .= '<div class="col"><h2 class="h6 fw-semibold mb-3">관리</h2><ul class="list-unstyled d-grid gap-2 small">';
        $html .= '<li><a class="text-decoration-none text-body-secondary" href="' . smartcms_h(smartcms_base_url('/admin/')) . '">관리자</a></li>';
        $html .= '<li><a class="text-decoration-none text-body-secondary" href="' . smartcms_h(smartcms_base_url('/admin/dashboard/')) . '">대시보드</a></li>';
        $html .= '<li><a class="text-decoration-none text-body-secondary" href="' . smartcms_h(smartcms_base_url('/admin/settings/')) . '">환경 설정</a></li>';
        $html .= '</ul></div>';
        $html .= '</div>';
        $html .= '<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 pt-4 mt-4 border-top small text-body-secondary">';
        $html .= '<span>&copy; ' . smartcms_h($year) . ' smartcms</span>';
        $html .= '<span>Naver-style clean dashboard</span>';
        $html .= '</div>';
        $html .= '</div></footer></main>';

        return $html;
    }
}

if (!function_exists('smartcms_admin_footer')) {
    function smartcms_admin_footer(): string
    {
        return '</div>'
             . '</section>'
             . '</div>'
             . '</div>'
             . '<footer class="mt-auto bg-white border-top">'
             . '<div class="container-fluid py-3 d-flex flex-column flex-md-row justify-content-between gap-2 small text-body-secondary">'
             . '<span>&copy; ' . smartcms_h(date('Y')) . ' smartcms admin</span>'
             . '<a href="' . smartcms_h(smartcms_base_url('/')) . '" class="text-decoration-none">사이트 홈</a>'
             . '</div>'
             . '</footer>'
             . '</main>';
    }
}

if (isset($SMARTCMS_FOOT) && is_array($SMARTCMS_FOOT)) {
    $scripts = (array)($SMARTCMS_FOOT['scripts'] ?? []);

    echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>' . PHP_EOL;
    foreach ($scripts as $script) {
        echo '<script src="' . smartcms_h(smartcms_asset_url((string)$script)) . '"></script>' . PHP_EOL;
    }
    echo '</body></html>';
}
