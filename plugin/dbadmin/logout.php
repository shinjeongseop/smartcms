<?php
define('SKIP_AUTH', true);
require_once __DIR__ . '/common.php';
$_SESSION['myadmin_auth'] = false;
$_SESSION['dbadmin_auth'] = false;
session_destroy();
header('Location: ' . DBADMIN_URL . '/login.php');
exit;
