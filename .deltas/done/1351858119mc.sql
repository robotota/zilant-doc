delete from  __field_config where table_name="users" and name="password";
insert into __field_config (table_name, name, display_name, type, display, editable) values ('users', 'password', 'Пароль', 'password', 0, 1);

