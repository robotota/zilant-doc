<?php

class FPAbstractReference extends FPDefault{

    
    public function control($selected, $append_none = False){
        global $auth;
        
    
    print '<span class="ui-widget">';
    print "<select id='{$this->getControlName()}' name='{$this->getControlName()}'>";
    
		if ($this->field->foreign_name == '')
			$this->field->foreign_name = 'name';
		if ($append_none){
            $is_selected = empty($selected)?'selected':'';
            print "<option value='' $is_selected> не выбрано </option>";
        }    
        $sql = "select id, ({$this->field->foreign_name}) as name from {$this->field->foreign_table_name}";


        $params = array();
        if ($this->opt_current_user_id){
            $sql .= " where id=:current_user_id";
            $params['current_user_id'] = $_SESSION['user_id'];
        }
		else
        if ($this->opt_filter_current_user_id){
            $sql .= " where user_id=:current_user_id";
            $params['current_user_id'] = $_SESSION['user_id'];
        }

        $sql .= ' order by name';
		    
        $sth = Connection::getConnection()->prepare($sql);
        $result = $sth->execute($params);
        if ($result){
            while ($select_row = $sth->fetch()){
                $value = $select_row['id'];
                $is_selected = ($value == $selected)?'selected':'';
                print "<option value='$value' $is_selected> {$select_row['name']} </option>";
            }
        }
        
        print '</select>';
        print '</span>';
        
        print '<script> $(function() {$("#'.$this->getControlName().'" ).chosen();});</script>';
    
    }
    public function controlNew(){
        if (!$this->field->isEditable()){
            print $this->viewValue($this->value);
            print "<input type=hidden id='{$this->getControlName()}' name='{$this->getControlName()}' value='{$this->value}'/> ";
            return;
        }

        $this->control($this->value, $this->opt_nullable);
    }

    public function controlEdit($row){
        if (!$this->field->isEditable()){

            print $this->viewValue($row[$this->field->name]);
            print "<input type=hidden id='{$this->getControlName()}' name='{$this->getControlName()}' value='{$row[$this->field->name]}'/> ";
            return;
        }

        $this->control($row[$this->field->name], $this->opt_nullable);
    }
 
    public function viewValue($value){
       if (!empty($this->field->foreign_table_name)){
	    
            $table = Table::getTable($this->field->foreign_table_name, getModuleInstance($this->field->foreign_table_name));
            $name_expr = empty($this->field->foreign_name) ? 'name' : $this->field->foreign_name;
            
            $name = $table->loadObjectValue($name_expr, $value);
            
            if (!is_null($name)){
                if (!empty($this->module->filter))
		            $parent_module = $this->module->parent_module;
                else
                      $parent_module = $this->module->metadata->table_name;
                 
                print hlink(call_sub($this->field->foreign_table_name, $parent_module, $this->module->parent_id, '', array('mode'=>'edit', 'id'=>$value)), $name);

            }        
            else
            if (!$this->opt_nullable)            
               print( $value.' - битая ссылка'); 
    }
  }

   public function view($row){
        $value = $row[$this->field->name];
        $this->viewValue($value);
  }    
}

?>
