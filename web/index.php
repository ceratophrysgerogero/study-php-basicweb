<?php

require('../app/functions.php');
$title = 'ログイン画面';
include('../app/_parts/_header.php');

session_save_path('/var/lib/php/session');
session_start();

$errorMessage = "";

// ログインボタンが押された場合
if (isset($_POST["login"])) {
    // １．ユーザIDの入力チェック
    if (empty($_POST["userid"])) {
        $errorMessage = "ユーザIDが未入力です。";
    } else if (empty($_POST["password"])) {
        $errorMessage = "パスワードが未入力です。";
    }
    // ２．ユーザIDとパスワードが入力されていたら認証する
    if (!empty($_POST["userid"]) && !empty($_POST["password"])) {

        // mysqlへの接続
        $mysqli = new mysqli('192.168.255.229', 'iwsk', 'Mysql02!', 'mydb');

        var_dump($mysqli);


        if ($mysqli->connect_errno) {
            print('<p>データベースへの接続に失敗しました。</p>' . $mysqli->connect_error);
            exit();
        }


        // データベースの選択
        $mysqli->select_db('mydb');

        // 入力値のサニタイズ
        $userid = $mysqli->real_escape_string($_POST["userid"]);

        // クエリの実行
        $query = "SELECT * FROM user WHERE name = '" . $userid . "'";
        $result = $mysqli->query($query);
        if (!$result) {
            print('クエリーが失敗しました。' . $mysqli->error);
            $mysqli->close();
            exit();
        }

        while ($row = $result->fetch_assoc()) {
            // パスワード(暗号化済み）の取り出し
            $db_hashed_pwd = $row['password'];
        }

        // データベースの切断
        $mysqli->close();

        // ３．画面から入力されたパスワードとデータベースから取得したパスワードのハッシュを比較します。
        //if ($_POST["password"] == $pw) {
        if (password_verify($_POST["password"], $db_hashed_pwd)) {
            // ４．認証成功なら、セッションIDを新規に発行する
            session_regenerate_id(true);
            $_SESSION["USERID"] = $_POST["userid"];
            header("Location: main.php");
            exit;
        } else {
            // 認証失敗
            $errorMessage = "ユーザIDあるいはパスワードに誤りがあります。";
        }
    } else {
        // 未入力なら何もしない
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
        <input type="submit" id="login" name="login" value="ログイン">
    </fieldset>
</form>
<a href="addUser.php">ユーザー情報登録ページへ</a>

<?php
include('../app/_parts/_footer.php');
?>
