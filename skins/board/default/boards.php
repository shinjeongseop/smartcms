<?php
/* 게시판 목록 스킨 - default/boards.php
 * 사용 가능 변수: $boards
 */
?>
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
  <?php foreach ($boards as $board): ?>
    <?php if ((string)$board['status'] === 'hidden') continue; ?>
    <div class="col">
      <a class="card h-100 text-decoration-none border-0 shadow-sm"
         href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">
        <div class="card-body p-4">
          <p class="text-uppercase small fw-semibold text-primary mb-2"><?= smartcms_h($board['board_key']) ?></p>
          <h2 class="h5 fw-bold mb-2"><?= smartcms_h($board['board_name']) ?></h2>
          <?php if (!empty($board['description'])): ?>
            <p class="text-body-secondary mb-0"><?= smartcms_h($board['description']) ?></p>
          <?php endif; ?>
        </div>
      </a>
    </div>
  <?php endforeach; ?>
  <?php if (!$boards): ?>
    <div class="col-12">
      <div class="alert alert-light border mb-0">사용 가능한 게시판이 없습니다.</div>
    </div>
  <?php endif; ?>
</div>
