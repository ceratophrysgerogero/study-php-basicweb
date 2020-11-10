DELIMITER //
CREATE PROCEDURE sample01()
BEGIN
  DECLARE v1 INT DEFAULT 99;
  DECLARE v2 INT DEFAULT 199;
  INSERT INTO user (name,password,mail,status,created_at,updated_at) VALUES (CONCAT('name',1),0,uuid(),1,now(),now());
  WHILE v1 > 0 DO
  INSERT INTO user (name,password,mail,status,created_at,updated_at) VALUES (CONCAT('name', last_insert_id()+1),0,uuid(),1,now(),now());
  SET v1 = v1 - 1;
  END WHILE;
  WHILE v2 > 99 DO
  INSERT INTO user (name,password,mail,status,created_at,updated_at) VALUES (CONCAT('name', last_insert_id()+1),0,uuid(),0,now(),now());
  SET v2 = v2 - 1;
  END WHILE;
END;
//
DELIMITER ;
/*
source /home/www/study-php-basicweb/mysql-textfile/create_test_user.sql
で登録
call sample01;
で実行
削除
DROP PROCEDURE sample01;

内容
name = name+挿入ID
password = 0
mail=ランダムユニーク数値(prykeyのため)
status = 0
created_at updated_at = 今
を100件追加する
status = 1
を100件追加する

解説
ストアドプロシージャ機能と呼ばれる
ストアドプロシージャの登録はCREATE～END;までの間で記述する
ストアプログラムはBEGINとENDで囲む必要がある。
delimiterは 「;」(デリミタ　終端文字)を //に変更して実行する為に必要
先頭の//と終盤の//は単純に書き方
BEGIN ~ END 内に, 複数行のプロシージャを記述

*/
