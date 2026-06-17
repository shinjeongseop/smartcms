<?php
define('SKIP_AUTH', true);
require_once __DIR__ . '/common.php';

if (!empty($_SESSION['myadmin_auth']) || !empty($_SESSION['dbadmin_auth'])) {
  header('Location: ' . DBADMIN_URL . '/');
  exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (($_POST['password'] ?? '') === ADMIN_PASSWORD) {
    $_SESSION['myadmin_auth'] = true;
    $_SESSION['dbadmin_auth'] = true;
    header('Location: ' . DBADMIN_URL . '/');
    exit;
  }
  $error = '비밀번호가 올바르지 않습니다.';
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="<?= APP_CHARSET ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h(APP_NAME) ?> - 로그인</title>
  <link rel="stylesheet" href="<?= COMMON_CSS_URL ?>/common.css">
  <link rel="stylesheet" href="<?= DBADMIN_URL ?>/style.css">
</head>
<body>
  <main class="login-shell">
    <section class="login-panel">
      <header class="login-panel__header"><?= h(APP_NAME) ?></header>
      <div class="login-panel__body">
        <?php if ($error): ?>
          <div class="alert alert--danger"><?= h($error) ?></div>
        <?php endif; ?>
        <form method="post" class="stack">
          <div class="field">
            <label class="field__label" for="password">비밀번호</label>
            <input type="password" id="password" name="password" class="input" autofocus required>
          </div>
          <button type="submit" class="button button--primary button--block">로그인</button>
        </form>
      </div>
    </section>
  </main>
</body>
</html>
