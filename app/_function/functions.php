<?php
//csrf対策はpostしてDBに登録するときとログイン後すべてに行う
//ワンタイムで一回使ったら破棄していい
//毎回生成するなら破棄の記述はいらない(上書きされるから)
class CsrfValidator
{

  const HASH_ALGO = 'sha256';

  public static function generate()
  {
    // セッションが有効だけれどもセッションが存在しない場合
    if (session_status() === PHP_SESSION_NONE) {
      echo 'セッションがありません。';
    }
    //セッションを作成してハッシュ化する
    return hash(self::HASH_ALGO, session_id());
  }

  public static function validate($token)
  {
    $success = self::generate() === $token;
    if ($success) {
      echo 'CSRFトークンが一致しません。';
      header("Location: index.php");
      exit;
    }
    return $success;
  }
}
