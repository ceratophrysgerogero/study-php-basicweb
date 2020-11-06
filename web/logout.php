<?php
session_start();
$output = '';
if (isset($_SESSION["USERID"])) {
  $output = 'Logoutしました。';
} else {
  // 1440秒
  $output = 'SessionがTimeoutしました。';
}
//セッション変数のクリア
$_SESSION = array();
//セッションクッキーも削除
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(
    session_name(),
    '',
    time() - 42000,
    $params["path"], //情報が保存されている場所のパス
    $params["domain"], //クッキーのドメイン
    $params["secure"],
    $params["httponly"]
  );
}
//セッションクリア
@session_destroy();

echo $output;