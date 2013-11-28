<?php
    class FPfile extends FPDefault{

        public function controlNew(){
          print "<input name=\"MAX_FILE_SIZE\" type=\"hidden\" value=\"10000000\"><br/>";
		  print "<input name=\"userfile\" type=\"file\"><br/>";
        }
        
        public function controlEdit($row){
          print $row[$this->field->name];
        }
        
        protected function displayText($row){
           if (preg_match("/[\.pdf^]|[\.jpg]/ui", $row['original_filename']) or empty($row['original_filename'])) 
           return "<img class=\"preview\" src=\"".call($this->module->metadata->table_name, 'preview', array("id" => $row['id']))."\" alt=\"Скачать\"/>";

           else
              return "Скачать";
        }
        public function view($row){
         print hlink(call($this->module->metadata->table_name, 'download', array("id" => $row['id'])), $this->displayText($row) );
        }
    }

?>