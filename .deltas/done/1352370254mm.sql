alter table __field_config modify column display boolean;
replace into __field_config (table_name, name, type, display_name, display, editable)
values ('__field_config', 'display', 'boolean', 'Показывать', 1, 1);
 
alter table __field_config modify column editable boolean;
replace into __field_config (table_name, name, type, display_name, display, editable)
values ('__field_config', 'editable', 'boolean', 'Редактируемое', 1, 1);

alter table __field_config add display_in_grid boolean after display;
update __field_config set display_in_grid = display;
replace into __field_config (table_name, name, type, display_name, display, display_in_grid, editable)
values ('__field_config', 'display_in_grid', 'boolean', 'Показывать в таблице', 1, 1, 1);


