<?php
/* 게시판 목록 스킨 - default/boards.php
 * 사용 가능 변수: $boards
 */
?>
<div class="sc-card-grid">
  <?php foreach ($boards as $board): ?>
    <?php if ((string)$board['status'] === 'hidden') continue; ?>
    <a class="card sc-card-link"
       href="<?= smartcms_h(smartcms_board_url((string)$board['board_key'])) ?>">
      <span class="sc-eyebrow"><?= smartcms_h($board['board_key']) ?></span>
      <strong><?= smartcms_h($board['board_name']) ?></strong>
      <?php if (!empty($board['description'])): ?>
        <span><?= smartcms_h($board['description']) ?></span>
      <?php endif; ?>
    </a>
  <?php endforeach; ?>
  <?php if (!$boards): ?>
    <p class="sc-empty">사용 가능한 게시판이 없습니다.</p>
  <?php endif; ?>
</div>
