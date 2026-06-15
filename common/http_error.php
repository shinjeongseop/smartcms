<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function smartcms_render_access_denied_page(string $message = '이 페이지를 볼 권한이 없습니다.'): void
{
    $current_user = function_exists('smartcms_current_user') ? smartcms_current_user() : null;
    $is_logged_in = is_array($current_user) && !empty($current_user);
    $home_url = smartcms_base_url('/');
    $secondary_url = $is_logged_in ? smartcms_base_url('/member/mypage/') : smartcms_base_url('/member/login/');
    $secondary_label = $is_logged_in ? '마이페이지' : '로그인';

    $SMARTCMS_HEAD = [
        'title' => '접근 권한 없음',
        'body_class' => 'bg-light',
        'main_class' => 'flex-grow-1 d-flex align-items-center',
    ];

    require SMARTCMS_ROOT . '/head.php';
    ?>
    <section class="container-fluid container-xxl py-5">
      <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 col-xl-6">
          <article class="card border shadow-sm overflow-hidden">
            <header class="card-header bg-danger-subtle text-danger p-4">
              <div class="d-flex align-items-center gap-3">
                <div class="badge bg-white text-danger p-3 rounded-3 shadow-sm">
                  <i class="bi bi-shield-lock-fill fs-4"></i>
                </div>
                <div>
                  <p class="text-uppercase small fw-bold text-danger-emphasis mb-1">Access Denied</p>
                  <h1 class="h3 fw-bold mb-0">접근 권한 없음</h1>
                </div>
              </div>
            </header>
            <div class="card-body p-4 p-md-5">
              <p class="text-body-secondary fw-medium mb-4"><?= smartcms_h($message) ?></p>
              <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-primary px-4 py-2 fw-bold shadow-sm" href="<?= smartcms_h($home_url) ?>">홈으로</a>
                <a class="btn btn-light border text-primary px-4 py-2 fw-bold shadow-none" href="<?= smartcms_h($secondary_url) ?>"><?= smartcms_h($secondary_label) ?></a>
              </div>
            </div>
          </article>
        </div>
      </div>
    </section>
    <?php
    require SMARTCMS_ROOT . '/foot.php';
    exit;
}
