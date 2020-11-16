<?php
include('../app/_parts/_header.php');
session_start();

$title = '本登録';

//ログアウトしてないと遷移できない
if (isset($_SESSION["user_mail"])) {
  header("Location: mypage.php");
  exit;
}

//成功・エラーメッセージの初期化
$errors = array();

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

if (empty($_GET)) {
  header("Location: registration_mail");
  exit();
} else {
  //GETデータを変数に入れる
  $urltoken = isset($_GET["urltoken"]) ? $_GET["urltoken"] : NULL;
  //メール入力判定
  if ($urltoken == '') {
    $errors['urltoken'] = "トークンがありません。";
  } else {
    try {
      // DB接続
      //flagが0の未登録者 and 仮登録日から24時間以内
      $sql = "SELECT mail FROM provisional_user WHERE urltoken=(:urltoken) AND flag =0 AND date > now() - interval 24 hour";

      $stm = $pdo->prepare($sql);
      $stm->bindValue(':urltoken', $urltoken, PDO::PARAM_STR);
      $stm->execute();

      //レコード件数取得　正常ならレコードは１件しかこないはず
      $row_count = $stm->rowCount();
      if ($row_count == 1) {
        $mail_array = $stm->fetch();
        //mailカラムを取り出す
        $mail = $mail_array["mail"];
      } else {
        $errors['urltoken_timeover'] = "このURLはご利用できません。有効期限が過ぎたかURLが間違えている可能性がございます。もう一度登録をやりなおして下さい。";
      }
      //データベース接続切断
      $stm = null;
    } catch (PDOException $e) {
      print('Error:' . $e->getMessage());
      die();
    }
  }
}

/**
 * 確認する(btn_confirm)押した後の処理
 */
if (isset($_POST['btn_confirm'])) {

  //セッショントークン生成
  $_SESSION['token'] = CsrfValidator::generate();
  $token = $_SESSION['token'];

  //POSTされたデータを各変数に入れる
  $name = isset($_POST['name']) ? $_POST['name'] : NULL;
  $password = isset($_POST['password']) ? $_POST['password'] : NULL;

  //セッションに登録
  $_SESSION['user_name'] = $name;
  $_SESSION['password'] = $password;
  $_SESSION['mail'] = $mail;

  //アカウント入力判定
  //パスワード入力判定
  if ($password == '') :
    $errors['password'] = "パスワードが入力されていません。";
  else :
    $password_hide = str_repeat('*', strlen($password));
  endif;

  if ($name == '') :
    $errors['name'] = "氏名が入力されていません。";
  endif;
}

/**
 * page_3
 * 登録(btn_submit)押した後の処理
 */
if (isset($_POST['btn_submit'])) {

  //csrf検出
  CsrfValidator::validate($token);

  //パスワードのハッシュ化
  $password_hash =  password_hash($_SESSION['password'], PASSWORD_DEFAULT);

  //本登録する(データベースに登録する)
  try {
    $sql = "INSERT INTO user (name,password,mail,status,created_at,updated_at) VALUES (:name,:password_hash,:mail,1,now(),now())";
    $stm = $pdo->prepare($sql);
    //mailのセッション名前変更
    unset($_SESSION['mail']);
    $_SESSION['user_mail'] = $mail;
    $stm->bindValue(':name',  $_SESSION['user_name'], PDO::PARAM_STR);
    $stm->bindValue(':mail', $_SESSION['user_mail'], PDO::PARAM_STR);
    $stm->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
    $stm->execute();

    //pre_userのflagを1にする(トークンの無効化)
    //一つのメールで複数回仮登録しているレコードをすべて無効化する
    $sql = "UPDATE provisional_user SET flag=1 WHERE mail=:mail";
    $stm = $pdo->prepare($sql);
    $stm->bindValue(':mail', $mail, PDO::PARAM_STR);
    $stm->execute();


    $mailTo = $mail . ',' . $companymail;
    $body = <<< EOM
       この度はご登録いただきありがとうございます。
       本登録致しました。
EOM;
    mb_language('ja');
    mb_internal_encoding('UTF-8');

    //Fromヘッダーを作成
    $registation_subject = "登録完了";

    $header = 'From: iwasaki@centsys.jp' . "\r\n";
    $header .= 'Return-Path: iwasaki@centsys.jp';
    $from = "iwasaki@centsys.jp";
    $pfrom   = "-f $from";

    if (mb_send_mail($mailTo, $registation_subject, $body, $header, $pfrom)) {
      $message['success'] = "会員登録しました";
    } else {
      $errors['mail_error'] = "メールの送信に失敗しました。";
    }

    //データベース接続切断
    $stm = null;
    header("Location: mypage.php");
  } catch (PDOException $e) {
    //トランザクション取り消し（ロールバック）
    $pdo->rollBack();
    $errors['error'] = "もう一度やりなおして下さい。";
    print('Error:' . $e->getMessage());
    //TODO 数秒後に遷移する機能を追加する
  }
}

?>

<h1>会員登録画面</h1>

<!-- page_3 完了画面-->
<?php if (isset($_POST['btn_submit']) && count($errors) === 0) : ?>
  本登録されました。

  <!-- page_2 確認画面-->
<?php elseif (isset($_POST['btn_confirm']) && count($errors) === 0) : ?>
  <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>?urltoken=<?php print $urltoken; ?>" method="post">
    <p>メールアドレス：<?= htmlspecialchars($_SESSION['mail'], ENT_QUOTES) ?></p>
    <p>パスワード：<?= $password_hide ?></p>
    <p>氏名：<?= htmlspecialchars($name, ENT_QUOTES) ?></p>

    <!-- 戻るボタンを押下するとbtn_confirmの値がなくなるので確認画面が表示されない -->
    <input type="submit" name="btn_back" value="戻る">
    <input type="hidden" name="token" value="<?= $_POST['token'] ?>">
    <input type="submit" name="btn_submit" value="登録する">
  </form>

<?php else : ?>
  <!-- page_1 登録画面(初期画面) -->
  <?php if (count($errors) > 0) : ?>
    <?php
    foreach ($errors as $value) {
      echo "<p class='error'>" . $value . "</p>";
    }
    ?>
  <?php endif; ?>

  <?php if (!isset($errors['urltoken_timeover'])) : ?>
    <!-- $_SERVER['SCRIPT_NAME']は現在のファイル名 -->
    <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>?urltoken=<?php print $urltoken; ?>" method="post">
      <!-- htmlspecialcharsはエスケープ処理　xss対策?　いらない気がする -->
      <p>メールアドレス：<?= htmlspecialchars($mail, ENT_QUOTES, 'UTF-8') ?></p>
      <p>パスワード：<input type="password" name="password"></p>
      <!-- 入力値を画面が離れるまで保持 -->
      <p>氏名：<input type="text" name="name" value="<?php if (!empty($_SESSION['user_name'])) {
                                                    echo  $_SESSION['user_name'];
                                                  } ?>"></p>
      <!-- 隠しフォームでトークンを入れる -->
      <input type="hidden" name="token" value="<?= $token ?>">
      <input type="submit" name="btn_confirm" value="確認する">
    </form>
  <?php endif ?>
<?php endif; ?>

<?php
include('../app/_parts/_footer.php');
?>
