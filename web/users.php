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


//ページネーションを表示
<?php
for ($n = 1; $n <= $pages; $n++) {
  if ($n == $now) {
    echo "<span style='padding: 5px;'>$now</span>";
  } else {
    echo "<a href='./home.php?page_id=$n' style='padding: 5px;'>$n</a>";
  }
}
?>


<?php
include('../app/_parts/_footer.php');
?>
