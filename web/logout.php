<?php
$title = 'ログアウト';
include('../app/_parts/_header.php');
session_start();
$output = '';
if (isset($_SESSION["userid"])) {
  $output = 'ログアウトしました。';
} else {
  // 1440秒
  $output = 'セッションがタイムアウトです。';
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
?>

<meta http-equiv="refresh" content=" 3; url=index.php">
<p>3秒後にログイン画面に遷移します。</p>

<?php
include('../app/_parts/_footer.php');
?>
