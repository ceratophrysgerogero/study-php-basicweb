<?php
$title = 'ログイン';
session_name();
session_start();
include('../app/_parts/_header.php');



//成功・エラーメッセージの初期化
$errorMessage =  "";

//DB情報
$user = 'iwsk';
$password = 'Mysql02!';
$dbName = "mydb";
$host = "192.168.255.229";

// ログインボタンが押された場合
if (isset($_POST["login"])) {

    //csrf検出
    CsrfValidator::validate($token);

    // １．ユーザIDの入力チェック
    if (empty($_POST["userid"])) {
        $errorMessage = "ユーザIDが未入力です。";
    } else if (empty($_POST["password"])) {
        $errorMessage = "パスワードが未入力です。";
    }

    // ２．ユーザIDとパスワードが入力されていたら認証する
    if (!empty($_POST["userid"]) && !empty($_POST["password"])) {
        try {
            //DB接続
            $dsn = "mysql:host={$host};dbname={$dbName};charser=utf8";
            $pdo = new PDO($dsn, $user, $password);
        } catch (PDOException $error) {
            //エラーの場合はエラーメッセージを吐き出す
            exit("データベースに接続できませんでした。<br>" . $error->getMessage());
        }
        //pdoの設定
        //メリットデメリット両方あるが細かい話なのでtrue falseどちらでもよさそう
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        //例外をスローする
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //sql文セット
        $sql = "SELECT * FROM user WHERE mail = :userid";
        $stm = $pdo->prepare($sql);
        $userid = isset($_POST['userid']) ? $_POST['userid'] : NULL;
        $stm->bindValue(':userid', $userid, PDO::PARAM_STR);
        //クエリ実行
        $stm->execute();
        $result = $stm->fetch(PDO::FETCH_ASSOC);

        if (empty($result)) {
            $errorMessage = "IDあるいはパスワードに誤りがあります。";
            // データベースの切断
            $stm  = null;
            $pdo = null;
        }

        // パスワード(暗号化済み）の取り出し
        $db_hashed_pwd = $result['password'];

        // データベースの切断
        $stm  = null;
        $pdo = null;

        //パスワードとデータベースから取得したパスワードのハッシュを比較
        if (password_verify($_POST["password"], $db_hashed_pwd)) {
            // セッションIDを新規に発行する
            session_regenerate_id(true);
            $_SESSION["userid"] = $_POST["userid"];
            header("Location: mypage.php");
            exit;
        } else {
            $errorMessage = "ユーザIDあるいはパスワードに誤りがあります。";
        }
    }
}

?>

<h1>ログイン</h1>
<form id="loginForm" name="loginForm" action="" method="POST">
    <fieldset>
        <legend>ログインフォーム</legend>
        <div><?php echo $errorMessage ?></div>
        <label for="userid">ユーザID</label>
        <input type="text" id="userid" name="userid" value="<?php echo htmlspecialchars($_POST["userid"], ENT_QUOTES); ?>">
        <br>
        <label for="password">パスワード</label><input type="password" id="password" name="password" value="">
        <br>
        <input type="hidden" name="token" value="<?= $token ?>">
        <input type="submit" id="login" name="login" value="ログイン">
    </fieldset>
</form>
<a href="addUser.php">ユーザー情報登録ページへ</a>

<?php
include('../app/_parts/_footer.php');
?>
