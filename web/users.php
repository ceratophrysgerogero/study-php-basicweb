<?php

session_start();
$title = 'ユーザー一覧';
include('../app/_parts/_header.php');

//ログインチェック
CsrfValidator::loginCheck();

//csrf検出
CsrfValidator::validate($token);


//クロスサイトリクエストフォージェリ（CSRF）対策
$_SESSION['token'] = CsrfValidator::generate();
$token = $_SESSION['token'];

//成功・エラーメッセージの初期化
$errorMessage =  "";

//DB情報
$user = 'iwsk';
$password = 'Mysql02!';
$dbName = "mydb";
$host = "192.168.255.229";

try {
  //DB接続
  $dsn = "mysql:host={$host};dbname={$dbName};charser=utf8";
  $pdo = new PDO($dsn, $user, $password);
} catch (PDOException $error) {
  //エラーの場合はエラーメッセージを吐き出す
  exit("データベースに接続できませんでした。<br>" . $error->getMessage());
}

//pdoの設定
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//一ページに表示する記事の数をmax_viewに定数として定義
define('max_view', 7);

//必要なページ数を求める カラム名をcountに変更する
$count = $pdo->prepare('SELECT COUNT(*) AS count FROM user WHERE status=1');
$count->execute();
$total_count = $count->fetch(PDO::FETCH_ASSOC);
//表示するページを計算
$pages = ceil($total_count['count'] / max_view);

//現在いるページのページ番号を取得
//ページがないところを指定してきたらusers.phpに移す
if (!isset($_GET['page_id'])) {
  $now = 1;
} elseif ($_GET['page_id'] <= 0 || $_GET['page_id'] > $pages) {
  header('Location: users.php');
} else {
  $now = $_GET['page_id'];
}


//表示する記事を取得するSQLを準備
//名前順でソートして登録されている名前を受け取る
$select = $pdo->prepare("SELECT name FROM user WHERE status=1 ORDER BY name ASC LIMIT :start,:max");
if ($now == 1) {
  //1ページ目の処理
  $select->bindValue(":start", $now - 1, PDO::PARAM_INT);
  $select->bindValue(":max", max_view, PDO::PARAM_INT);
} else {
  //1ページ目以外の処理
  $select->bindValue(":start", ($now - 1) * max_view, PDO::PARAM_INT);
  $select->bindValue(":max", max_view, PDO::PARAM_INT);
}
//実行し結果を取り出しておく
$select->execute();
$data = $select->fetchAll(PDO::FETCH_ASSOC);


?>

<h1>ユーザー一覧</h1>
<ul>
  <?php
  // ユーザー一覧表示
  foreach ($data as $row) {
    echo "<li>$row[name]</a></li>";
  }
  ?>
</ul>

<?php
//ページネーションを表示
for ($n = 1; $n <= $pages; $n++) {
  //表示されているページ数と一致したら
  if ($n == $now) {
    echo "<span style='padding: 5px;'>$now</span>";
  } else {
    //getパラメーターを使って押されたページ数に遷移させる
    echo "<a href='./users.php?page_id=$n' style='padding: 5px;'>$n</a>";
  }
}
?>

<?php
include('../app/_parts/_footer.php');
?>
