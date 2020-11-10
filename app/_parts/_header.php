<?php
//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');
//スタティックな共通関数
include('../app/_function/functions.php');
//セッション保存場所指定
session_save_path('/var/lib/php/session');
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES, "UTF-8"); ?></title>
    <?php
    if (isset($_SESSION["userid"])) {
        echo "<div align='right'>
        <a href='mypage.php'>マイページ</a>
        <a href='users.php'>ユーザー一覧</a>
        <a href='logout.php'>ログアウト</a>
        </div>";
    }

    ?>

</head>

<body>
