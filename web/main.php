<?php
session_start();

// ログイン状態のチェック
if (!isset($_SESSION["userid"])) {
  header("Location: logout.php");
  exit;
}

?>

<h1>メイン</h1>
<!-- ユーザIDにHTMLタグが含まれても良いようにエスケープする -->
<p>ようこそ<?= htmlspecialchars($_SESSION["userid"], ENT_QUOTES); ?>さん</p>
<ul>
  <li><a href="logout.php">ログアウト</a></li>
</ul>
