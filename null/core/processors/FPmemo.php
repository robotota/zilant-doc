<?php
    class FPmemo extends FPDefault{

        public function controlNew(){
            print ("<textarea name=\"{$this->getControlName()}\"></textarea>");
        }
        
        public function controlEdit($row){
            print ("<textarea name=\"{$this->getControlName()}\">{$row[$this->field->name]}</textarea>");
        }
        
        public function getCondition($filter_values){
            if (isset($filter_values[$this->getControlName()]))
                return $this->getControlName().' like concat(\'%\', :'.$this->getControlName().',\'%\') ';
            else
                return null;
        }                    
    }

?>