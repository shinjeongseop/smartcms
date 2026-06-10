<?php
declare(strict_types=1);

require_once __DIR__ . '/common/config.php';

if (!function_exists('smartcms_site_footer')) {
    function smartcms_site_footer(): string
    {
        $year = date('Y');

        $html  = '<footer class="bg-dark text-white-50 py-5 mt-auto">';
        $html .= '<div class="container-xxl">';
        $html .= '<div class="row g-4">';
        $html .= '<div class="col-12 col-lg-4">';
        $html .= '<a class="navbar-brand fs-3 fw-bold text-white d-block mb-3" href="' . smartcms_h(smartcms_base_url('/')) . '">';
        $html .= 'smartcms<span class="text-primary">.</span>';
        $html .= '</a>';
        $html .= '<p class="small mb-4">Bootstrap 5 Native Community CMS<br>모던한 기술로 구축하는 커뮤니티의 새로운 기준</p>';
        $html .= '<div class="d-flex gap-3 fs-5 text-white">';
        $html .= '<i class="bi bi-github"></i>';
        $html .= '<i class="bi bi-discord"></i>';
        $html .= '<i class="bi bi-youtube"></i>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="col-6 col-lg-2 offset-lg-2">';
        $html .= '<h3 class="h6 fw-bold text-white mb-3">Service</h3>';
        $html .= '<ul class="list-unstyled small d-grid gap-2">';
        $html .= '<li><a href="' . smartcms_h(smartcms_base_url('/board/')) . '" class="text-decoration-none text-reset">전체 게시판</a></li>';
        $html .= '<li><a href="' . smartcms_h(smartcms_base_url('/member/login/')) . '" class="text-decoration-none text-reset">로그인</a></li>';
        $html .= '<li><a href="' . smartcms_h(smartcms_base_url('/member/register/')) . '" class="text-decoration-none text-reset">회원가입</a></li>';
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '<div class="col-6 col-lg-2">';
        $html .= '<h3 class="h6 fw-bold text-white mb-3">Support</h3>';
        $html .= '<ul class="list-unstyled small d-grid gap-2">';
        $html .= '<li><a href="#" class="text-decoration-none text-reset">이용약관</a></li>';
        $html .= '<li><a href="#" class="text-decoration-none text-reset text-primary">개인정보처리방침</a></li>';
        $html .= '<li><a href="#" class="text-decoration-none text-reset">운영정책</a></li>';
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '<div class="col-6 col-lg-2">';
        $html .= '<h3 class="h6 fw-bold text-white mb-3">Admin</h3>';
        $html .= '<ul class="list-unstyled small d-grid gap-2">';
        $html .= '<li><a href="' . smartcms_h(smartcms_base_url('/admin/')) . '" class="text-decoration-none text-reset">관리자 홈</a></li>';
        $html .= '<li><a href="' . smartcms_h(smartcms_base_url('/admin/settings/')) . '" class="text-decoration-none text-reset">시스템 설정</a></li>';
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="border-top border-secondary border-opacity-25 pt-4 mt-5 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 small">';
        $html .= '<span>&copy; ' . smartcms_h($year) . ' smartcms. All rights reserved.</span>';
        $html .= '<span class="text-white-50">Powered by Bootstrap 5 & PHP</span>';
        $html .= '</div>';
        $html .= '</div></footer></main>';

        return $html;
    }
}

if (isset($SMARTCMS_FOOT) && is_array($SMARTCMS_FOOT)) {
    $is_admin = str_contains((string)($_SERVER['REQUEST_URI'] ?? ''), '/admin/');
    if ($is_admin && !str_contains((string)$_SERVER['REQUEST_URI'], '/admin/login/')) {
        echo '      </main>' . PHP_EOL;
        echo '    </div>' . PHP_EOL;
        echo '  </div>' . PHP_EOL;
    }
    $scripts = (array)($SMARTCMS_FOOT['scripts'] ?? []);

    echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>' . PHP_EOL;
    foreach ($scripts as $script) {
        echo '<script src="' . smartcms_h(smartcms_asset_url((string)$script)) . '"></script>' . PHP_EOL;
    }
    echo '</body></html>';
}
