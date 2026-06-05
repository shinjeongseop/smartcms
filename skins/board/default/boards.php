<section class="smartcms-card-grid">
  <?php foreach ($boards as $item): ?>
    <?php if ((string)$item['status'] !== 'hidden'): ?>
      <a class="smartcms-card-link" href="<?= smartcms_h(smartcms_board_url((string)$item['board_key'])) ?>">
        <strong><?= smartcms_h($item['board_name']) ?></strong>
        <span><?= smartcms_h($item['description'] ?? '게시판으로 이동') ?></span>
      </a>
    <?php endif; ?>
  <?php endforeach; ?>
  <?php if (!$boards): ?>
    <?= smartcms_alert('생성된 게시판이 없습니다.', 'info') ?>
  <?php endif; ?>
</section>
