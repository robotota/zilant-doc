<?php
class FPpassword extends FPDefault{

    public function controlEdit($row){
        print "<input name=\"{$this->getControlName()}\" value=\"\">";
    }

    public function sanitize($value){
    	if (empty($value))
    	    return null;
    	else 
    	 return md5(parent::sanitize($value));

    }

}



?>