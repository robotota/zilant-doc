alter table users add column user_hash varchar(200);
insert into __field_config (foreign_table_name, table_name, name, editable, display_name, display_in_grid, type, display, foreign_name, options) values ( "", "users", "user_hash", "0", "Хэш", "0", "string", "0", "", "" ) on duplicate key update foreign_table_name = "", table_name = "users", name = "user_hash", editable = "0", display_name = "Хэш", display_in_grid = "0", type = "string", display = "0", foreign_name = "", options = "";

