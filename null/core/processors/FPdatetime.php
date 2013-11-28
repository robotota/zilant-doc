<?php
    class FPdatetime extends FPDefault{
    
        
        public function applyStyle(){
        if ($this->field->isEditable())
            print '<script> $(function() {$("#'.$this->getControlName().'" ).datepicker();});</script>';        
        }
        public function controlNew(){
            parent::controlNew();
            $this->applyStyle();

        }
        
        public function controlEdit($row){
            parent::controlEdit($row);
            $this->applyStyle();
        }
        
//        public function controlFilter($value){
//            list($year,$month, $day) = split('[/.-]', $value);
//            $this->control($year,$month,$day,True);
//        }
    }
?>