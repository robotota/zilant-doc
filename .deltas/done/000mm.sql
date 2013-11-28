alter table __display_names add unique key (table_name);
alter table __field_config modify column table_name varchar(50);
alter table __field_config modify column name varchar(50);
alter table __field_config add unique key (table_name, name);


