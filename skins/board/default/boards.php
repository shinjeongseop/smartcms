<?php
/* 게시판 목록 스킨 - default/boards.php
 * 사용 가능 변수: $boards
 */
?>
<section class="smartcms-board-registry py-4">
  <header class="mb-5 text-center">
    <h2 class="display-6 fw-bold text-dark mb-2">커뮤니티 게시판 목록</h2>
    <p class="text-secondary fw-medium">SmartCMS가 제공하는 다양한 소통 공간입니다.</p>
  </header>

  <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
    <?php foreach ($boards as $board): ?>
      <?php if ((string)$board['status'] === 'hidden') continue; ?>
      <div class="col">
        <article class="card h-100 border shadow-sm hover-shadow transition-all overflow-hidden border-top border-primary border-4">
          <div class="card-body p-4 p-lg-5">
            <div class="d-flex align-items-start justify-content-between mb-3">
              <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fw-bold text-uppercase letter-spacing-1" style="font-size: 0.7rem;">
                <?= smartcms_h($board['board_key']) ?>
              </span>
              <div class="text-secondary opacity-25"><i class="bi bi-chat-quote-fill fs-2"></i></div>
            </div>
            
            <h3 class="h4 fw-bold mb-3">
              <a href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>" class="text-decoration-none text-dark stretched-link">
                <?= smartcms_h($board['board_name']) ?>
              </a>
            </h3>

            <?php if (!empty($board['description'])): ?>
              <p class="text-secondary mb-0 small fw-medium lh-base"><?= smartcms_h($board['description']) ?></p>
            <?php else: ?>
              <p class="text-secondary mb-0 small opacity-50 italic">이 게시판에 대한 설명이 없습니다.</p>
            <?php endif; ?>
          </div>
          <footer class="card-footer bg-white border-top px-4 py-3 d-flex justify-content-between align-items-center">
            <span class="text-xs fw-bold text-uppercase text-secondary opacity-75">Level <?= (int)$board['board_list_level'] ?> Required</span>
            <i class="bi bi-arrow-right-circle-fill text-primary fs-5"></i>
          </footer>
        </article>
      </div>
    <?php endforeach; ?>

    <?php if (!$boards): ?>
      <div class="col-12">
        <aside class="alert alert-light border shadow-sm p-5 text-center">
          <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
          <p class="mb-0 fw-bold text-secondary">현재 운영 중인 게시판이 없습니다.</p>
        </aside>
      </div>
    <?php endif; ?>
  </div>
</section>

<style>
.transition-all { transition: all 0.3s ease; }
.hover-shadow:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.1) !important; }
</style>
