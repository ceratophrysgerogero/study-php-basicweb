<?php
session_start();
$title = 'ユーザー一覧';
include('../app/_parts/_header.php');
// ログイン状態のチェック
if (!isset($_SESSION["userid"])) {
  header("Location: logout.php");
  exit;
}

?>

<h1>ユーザー一覧</h1>

<ul>
  <li><a href="mypage.php">マイページ</a></li>
</ul>
