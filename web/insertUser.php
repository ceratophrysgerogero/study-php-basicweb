<html>
<meta charset="UTF-8">

<head>
  <title>ログインユーザー追加ページ</title>
</head>

<body>
  <?php
  require 'password.php';
  $mysqli = new mysqli('192.168.255.210', 'root', 'root');
  if ($mysqli->connect_errno) {
    print('<p>データベースへの接続に失敗しました。</p>' . $mysqli->connect_error);
    exit();
  }

  // データベースの選択
  $result_select_db = $mysqli->select_db('test');
  if (!$result_select_db) {
    print('データベースの選択に失敗しました。');
    $mysqli->close();
    exit();
  }

  mysqli_set_charset($link, 'utf8');

  $result = $mysqli->query('SELECT MAX(id) as maxid FROM MST_MEMBER');
  if (!$result) {
    print('SELECTクエリーが失敗しました。' . $mysqli->error);
    $mysqli->close();
    exit();
  }

  while ($row = $result->fetch_assoc()) {
    if (!ctype_digit($row['maxid']) && $row['maxid'] != null) {
      print('SELECTクエリーが失敗しました。');
      $mysqli->close();
      exit();
    }
    $maxid = intval($row['maxid']);
  }

  $maxid++;

  $name = filter_input(INPUT_POST, 'name');
  //入力文字をエスケープしてエスケープされればエラーにする
  $name_esc = addslashes($name);
  if (empty($name_esc)) {
    print('NAMEが入力されていません。<br><a href="add.html">戻る</a>');
    $mysqli->close();
    exit();
  }
  if ($name != $name_esc) {
    print('使用できない文字（\',\\,NULL,"）が含まれています。<br><a href="add.html">戻る</a>');
    $mysqli->close();
    exit();
  }
  $password = filter_input(INPUT_POST, 'password');
  if (empty($password)) {
    print('PASSWORDが入力されていません。<br><a href="add.html">戻る</a>');
    $mysqli->close();
    exit();
  }
  $hashpass = password_hash($password, PASSWORD_DEFAULT);

  $sql = "INSERT INTO MST_MEMBER (id, name, password) VALUES ($maxid,'$name_esc','$hashpass')";
  $result_flag = $mysqli->query($sql);

  if (!$result_flag) {
    print('クエリーが失敗しました。' . $mysqli->error . '<br><a href="add.html">戻る</a>');
    $mysqli->close();
    exit();
  }

  print('<p>ユーザー' . $name_esc . 'を登録しました。</p>');

  $close_flag = mysqli_close($link);

  ?>
  <a href="add.html">戻る</a>
</body>

</html>
