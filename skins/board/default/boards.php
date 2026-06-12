<?php
/* 게시판 목록 스킨 - default/boards.php
 * 사용 가능 변수: $boards
 */
$board_skin_meta = fn(array $item): array => smartcms_board_skin_meta($item);
?>
<section class="smartcms-board-registry py-4">
  <header class="mb-5 text-center">
    <h2 class="fs-5 fw-bold text-dark mb-2">커뮤니티 게시판 목록</h2>
    <p class="text-secondary fw-medium fs-6 mb-0">SmartCMS가 제공하는 다양한 소통 공간입니다.</p>
  </header>

  <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
    <?php foreach ($boards as $board): ?>
      <?php if ((string)$board['status'] === 'hidden') continue; ?>
      <?php $skin_meta = $board_skin_meta($board); ?>
      <div class="col">
        <article class="card h-100 border shadow-sm bg-white overflow-hidden <?= $skin_meta['header_class'] ?>">
          <div class="card-body p-4 p-lg-5">
            <div class="d-flex align-items-start justify-content-between mb-3">
              <span class="badge <?= $skin_meta['badge_class'] ?> rounded-pill px-3 py-2 fw-bold">
                <?= smartcms_h($board['board_key']) ?>
              </span>
              <div class="<?= $skin_meta['accent'] === 'dark' ? 'text-dark' : 'text-' . $skin_meta['accent'] ?> opacity-25">
                <i class="<?= smartcms_h((string)$skin_meta['icon']) ?> fs-2"></i>
              </div>
            </div>
            
            <h3 class="fs-5 fw-bold mb-3">
              <a href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>" class="text-decoration-none text-dark stretched-link">
                <?= smartcms_h($board['board_name']) ?>
              </a>
            </h3>

            <?php if (!empty($board['description'])): ?>
              <p class="text-secondary mb-0 fs-6 fw-medium lh-base"><?= smartcms_h($board['description']) ?></p>
            <?php else: ?>
              <p class="text-secondary mb-0 fs-6 opacity-50">이 게시판에 대한 설명이 없습니다.</p>
            <?php endif; ?>
          </div>
        </article>
      </div>
    <?php endforeach; ?>

    <?php if (!$boards): ?>
      <div class="col-12">
        <aside class="alert alert-light border shadow-sm p-5 text-center bg-white">
          <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
          <p class="mb-0 fw-bold text-secondary">현재 운영 중인 게시판이 없습니다.</p>
        </aside>
      </div>
    <?php endif; ?>
  </div>
</section>
