<?php
session_start();
$title = 'マイページ';
include('../app/_parts/_header.php');

include('../app/_function/functions.php');
//csrf検出
CsrfValidator::validate($token);

//セッショントークン生成
$_SESSION['token'] = CsrfValidator::generate();
$token = $_SESSION['token'];

//ログインチェック
CsrfValidator::loginCheck();

?>

<h1>メイン</h1>
<!-- ユーザIDにHTMLタグが含まれても良いようにエスケープする -->
<p>ようこそ<?= htmlspecialchars($_SESSION["userid"], ENT_QUOTES); ?>さん</p>
<ul>
  <li><a href="users.php">ユーザー一覧</a></li>
  <li><a href="logout.php">ログアウト</a></li>
</ul>

<?php
include('../app/_parts/_footer.php');
?>
