<?php

class Field extends ArrayObject{
	public function isVisible(){
	    global $auth;
		return $this->display || $auth->is_admin() || $this->name=='id' || $this->name == 'ID';
	}
	
	public function isGridVisible(){
	    return $this->display_in_grid;
	}

    public function isEditable(){
        return $this->editable;
    }
}

?>