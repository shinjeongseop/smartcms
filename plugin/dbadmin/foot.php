<?php
if (!defined('MYADMIN_ACCESS')) {
  exit;
}
?>
  <div class="modal" id="commonModal" hidden>
    <div class="modal__dialog modal__dialog--xl" id="commonModalDialog">
      <div class="modal__content" id="commonModalContent">
        <div class="modal__body text-center">로딩중...</div>
      </div>
    </div>
  </div>

  <div class="modal" id="messageModal" hidden>
    <div class="modal__dialog">
      <div class="modal__content">
        <div class="modal__header">
          <h2 class="modal__title">알림</h2>
          <button type="button" class="icon-btn" data-modal-close aria-label="닫기">×</button>
        </div>
        <div class="modal__body" id="messageModalBody"></div>
        <div class="modal__footer">
          <button type="button" class="button button--primary" data-modal-close>확인</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    const APP_CONFIG = { dbadminUrl: '<?= DBADMIN_URL ?>' };
  </script>
  <script src="<?= COMMON_JS_URL ?>/common.js"></script>
  <script src="<?= DBADMIN_URL ?>/ui.js"></script>
  <script src="<?= DBADMIN_URL ?>/app.js"></script>
</body>
</html>
