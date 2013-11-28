/* общий скрипт на создание пользователя и базы для него */
create database NAME;
create user NAME identified by 'NAME';
grant all on NAME.* to NAME;
