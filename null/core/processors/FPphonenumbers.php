<?php
    class FPphonenumbers extends FPDefault{
        public function controlNew(){
            printf('<input name="%s" value="%s"> <a href="tel:%s"> позвонить </a>', $this->getControlName(), $this->value, $this->value);
        }
        
        public function controlEdit($row){
            printf('<input name="%s" value="%s"> <a href="tel:%s"> позвонить </a>',$this->getControlName(), $row[$this->field->name], $row[$this->field->name]);
        }

        public function view($row){
            print '&nbsp <a href="tel:'.$row[$this->field->name].'">'.$row[$this->field->name].'</a>';
            if (!isset($row[$this->field->name]) || $row[$this->field->name]=='') print('&nbsp');
        }


    }

?>