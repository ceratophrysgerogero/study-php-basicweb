DROP TABLE IF EXISTS users;
create table users (
     id int not null auto_increment primary key,
     username varchar(100) not null,
     password varchar(255) not null
     );
insert into users values (1, 'sampleuser', 'samplepass');
DESC users;
SHOW TABLES;
select * from users;
