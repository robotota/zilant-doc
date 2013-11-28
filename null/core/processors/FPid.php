<?php

    class FPid extends FPDefault{

        public function controlNew(){
          print "Новый";
        }
        
        public function controlEdit($row){
           $value = $row[$this->field->name];
           print("<span>$value</span>");
           printf('<input name="%s" value="%s" type=hidden>',$this->getControlName(), $value);

        }
    }
?>