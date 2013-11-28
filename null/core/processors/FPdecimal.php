<?php

    class FPdecimal extends FPDefault{
        public function validate($value){
        //return empty($value) or is_numeric($value);
        return true;
        }
    }
?>