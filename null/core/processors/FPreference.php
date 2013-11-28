<?php

class FPreference extends FPAbstractReference{

      public function getCondition($filter_values){
        if (isset($filter_values[$this->getControlName()])){
        $name_expr = empty($this->field->foreign_name) ? 'name' : $this->field->foreign_name;
            return $this->getControlName()." in (select id from {$this->field->foreign_table_name} where upper(($name_expr)) like upper(concat('%', :{$this->getControlName()},'%')) )";}
        else
            return null;
        
        }                    
        
    
}
?>