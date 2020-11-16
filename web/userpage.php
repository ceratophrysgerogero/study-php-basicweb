<?php
session_start();
$title = 'ユーザーページ';


include('../app/_parts/_header.php');

//csrf検出
CsrfValidator::validate($token);

//セッショントークン生成
$_SESSION['token'] = CsrfValidator::generate();
$token = $_SESSION['token'];

//ログインチェック
CsrfValidator::loginCheck();

//DB情報
$user = 'iwsk';
$password = 'Mysql02!';
$dbName = "mydb";
$host = "192.168.255.229";

//DB接続
$dsn = "mysql:host={$host};dbname={$dbName};charser=utf8";
$pdo = new PDO($dsn, $user, $password);
//メリットデメリット両方あるが細かい話なのでtrue falseどちらでもよさそう
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
//例外をスローする
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
  $sql = "SELECT * FROM user WHERE id = :page_id";
  $stm = $pdo->prepare($sql);
  $stm->bindValue(':page_id', $_GET["page_id"], PDO::PARAM_STR);
  $stm->execute();
  $user_array = $stm->fetch();
  $user_id = $user_array['id'];
  $user_name = $user_array['name'];
  $user_email = $user_array['mail'];

  //データベース接続切断
  $stm = null;
} catch (PDOException $e) {
  print('Error:' . $e->getMessage());
  die();
}
?>

<?php

if ($_SESSION['user_mail'] === $user_email) {
  echo "<h1>マイページ</h1><a>ようこそ$user_name</a>さん";
} else {
  echo "<h1><a>$user_name</a>さんのページです</h1>";
}


?>


<?php
include('../app/_parts/_footer.php');
?>
