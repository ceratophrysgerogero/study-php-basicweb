<?php
session_start();
$title = 'マイページ';
include('../app/_parts/_header.php');

//csrf検出
CsrfValidator::validate($token);

//セッショントークン生成
$_SESSION['token'] = CsrfValidator::generate();
$token = $_SESSION['token'];

//ログインチェック
CsrfValidator::loginCheck();

?>

<h1>マイページ</h1>
<!-- ユーザIDにHTMLタグが含まれても良いようにエスケープする -->
<p>ようこそ<?= htmlspecialchars($_SESSION["user_mail"], ENT_QUOTES); ?>さん</p>


<?php
include('../app/_parts/_footer.php');
?>
