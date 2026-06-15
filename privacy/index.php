<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/config.php';

$SMARTCMS_HEAD = ['title' => '개인정보처리방침', 'main_class' => 'flex-grow-1 py-5'];
require SMARTCMS_ROOT . '/head.php';
?>

<section class="container-xxl">
  <article class="card border shadow-sm">
    <header class="card-header bg-white border-bottom py-4 px-4">
      <p class="small text-secondary fw-semibold mb-2">Privacy Policy</p>
      <h1 class="h3 fw-bold mb-0">개인정보처리방침</h1>
    </header>
    <div class="card-body p-4 p-lg-5">
      <div class="vstack gap-4">
        <section>
          <h2 class="h5 fw-bold">수집하는 정보</h2>
          <p class="mb-0 text-secondary">회원 가입과 게시판 운영에 필요한 이메일, 이름, 닉네임, 접속 기록 등 최소한의 정보를 수집합니다.</p>
        </section>
        <section>
          <h2 class="h5 fw-bold">이용 목적</h2>
          <p class="mb-0 text-secondary">수집된 정보는 회원 인증, 게시글 관리, 보안 로그 확인, 서비스 품질 개선을 위해 사용합니다.</p>
        </section>
        <section>
          <h2 class="h5 fw-bold">보관 및 파기</h2>
          <p class="mb-0 text-secondary">운영 목적이 종료되거나 회원 삭제 요청이 처리되면 관련 법령과 내부 보안 기준에 따라 정보를 파기합니다.</p>
        </section>
      </div>
    </div>
  </article>
</section>

<?php
$SMARTCMS_FOOT = [];
require SMARTCMS_ROOT . '/foot.php';
?>
