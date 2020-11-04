<?php
session_start();

//クロスサイトリクエストフォージェリ（CSRF）対策
$_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
$token = $_SESSION['token'];
//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

//DB情報
$user = 'iwsk'; //データベースユーザ名
$password = 'Mysql02!'; //データベースパスワード
$dbName = "mydb"; //データベース名
$host = "192.168.255.229"; //ホスト

//エラーメッセージの初期化
$errors = array();

//DB接続
$dsn = "mysql:host={$host};dbname={$dbName};charser=utf8";
//pdoオブジェクト生成
$pdo = new PDO($dsn, $user, $password);
//メリットデメリット両方あるが細かい話なのでtrue falseどちらでもよさそう
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
//例外をスローする
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);



//送信ボタンクリックした後の処理
if (isset($_POST['submit'])) {
  //メールアドレス空欄の場合
  if (empty($_POST['mail'])) {
    $errors['mail'] = 'メールアドレスが未入力です。';
  } else {
    //POSTされたデータを変数に入れる
    $mail = isset($_POST['mail']) ? $_POST['mail'] : NULL;

    //メールアドレス構文チェック
    if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $mail)) {
      $errors['mail_check'] = "メールアドレスの形式が正しくありません。";
    }

    //sql文を作成
    $sql = "SELECT id FROM user WHERE mail=:mail";
    //sql文セット(準備完了)
    $stm = $pdo->prepare($sql);
    //パラメーター :mailに値をセット
    $stm->bindValue(':mail', $mail, PDO::PARAM_STR);
    //クエリの実行
    $stm->execute();
    //結果を格納(入力されたメールが登録されていなかったら空になる)
    $result = $stm->fetch(PDO::FETCH_ASSOC);

    //user テーブルに同じメールアドレスがある場合、エラー表示
    if (isset($result["id"])) {
      $errors['user_check'] = "このメールアドレスはすでに利用されております。";
    }
  }

  //エラーがない場合、pre_userテーブルにインサート
  if (count($errors) === 0) {
    $urltoken = hash('sha256', uniqid(rand(), 1));
    $url = "http://localhost:8080/signup.php?urltoken=" . $urltoken;
    try {
      //仮登録テーブルに挿入
      $sql = "INSERT INTO provisional_user (urltoken, mail, date, flag) VALUES (:urltoken, :mail, now(), '0')";
      //sql文セット
      $stm = $pdo->prepare($sql);
      //パラメーターセット
      $stm->bindValue(':urltoken', $urltoken, PDO::PARAM_STR);
      $stm->bindValue(':mail', $mail, PDO::PARAM_STR);
      //sql実行
      $stm->execute();
      $pdo = null;
      $message = "メールをお送りしました。24時間以内にメールに記載されたURLからご登録下さい。";
    } catch (PDOException $e) {
      print('仮登録インサートエラー:' . $e->getMessage());
      //プログラム終了
      die();
    }

    $mailTo = $mail;
    // ヒアドキュメント変数に格納
    // <<<< ID(EOM end of Message)
    $body = <<< EOM
       この度はご登録いただきありがとうございます。
       24時間以内に下記のURLからご登録下さい。
       {$url}
EOM;

    //メール設定
    mb_language('ja');
    mb_internal_encoding('UTF-8');

    //Fromヘッダーを作成
    $registation_subject = "表題";

    $header = 'From: iwasaki@centsys.jp' . "\r\n";
    $header .= 'Return-Path: iwasaki@centsys.jp';
    $from = "iwasaki@centsys.jp";
    $pfrom   = "-f $from";

    if (mb_send_mail($mailTo, $registation_subject, $body, $header, $pfrom)) {
      //セッション変数のトークン破棄
      $_SESSION = array();
      //デフォルトセッション名破棄(クッキーの削除)
      if (isset($_COOKIE["PHPSESSID"])) {
        setcookie("PHPSESSID", '', time() - 1800, '/');
      }
      //セッションを破棄する
      session_destroy();
      $message = "メールをお送りしました。24時間以内にメールに記載されたURLからご登録下さい。";
    }
  }
}
?>


<h1>仮会員登録画面</h1>
<?php if (isset($_POST['submit']) && count($errors) === 0) : ?>
  <!-- 登録完了画面 -->
  <p><?= $message ?></p>
  <p>↓TEST用(後ほど削除)：このURLが記載されたメールが届きます。</p>
  <a href="<?= $url ?>"><?= $url ?></a>
<?php else : ?>
  <!-- 登録画面 -->
  <?php if (count($errors) > 0) : ?>
    <?php
    foreach ($errors as $value) {
      echo "<p class='error'>" . $value . "</p>";
    }
    ?>
  <?php endif; ?>
  <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="post">
    <p>メールアドレス：<input type="text" name="mail" size="50" value="<?php if (!empty($_POST['mail'])) {
                                                                  echo $_POST['mail'];
                                                                } ?>"></p>
    <input type="hidden" name="token" value="<?= $token ?>">
    <input type="submit" name="submit" value="送信">
  </form>
<?php endif; ?>
