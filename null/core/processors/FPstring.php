<?php
    class FPstring extends FPDefault{
      public function getCondition($filter_values){
            if (isset($filter_values[$this->getControlName()]))
                return "upper({$this->field->name}) like upper(concat('%', :{$this->getControlName()},'%'))";
            else
                return null;     
        }                    
    }

?>