<?php

    class FPDefault{
        public $field;
        public $module;
        public $value = null;
        
        public $opt_current_user_id = false;
        public $opt_filter_current_user_id = false;
        public $opt_current_date = false;
        public $opt_nullable = false;
        function __construct($field, $module){
            global $auth;
            $this->field = $field;
            $this->module = $module;

            $this->opt_current_user_id = preg_match("/(?<!=)current_user_id/", $this->field->options);
            $this->opt_filter_current_user_id = !$auth->is_admin() && preg_match("|filter\(user_id=current_user_id\)|", $this->field->options);
            $this->opt_current_date = preg_match("|current_date|", $this->field->options);
            $this->opt_nullable = preg_match("|nullable|", $this->field->options);
        }
        
        public function getControlName($id = 0){
            //debug_print_backtrace();
            return $this->module->getPrefix().$this->field->name;
        }
        
        public function controlNew(){
            
            printf('<input name="%s" id="'.$this->getControlName().'" value="%s" '.($this->field->isEditable()?'':'readonly="readonly"').'>', $this->getControlName(), $this->value);
        }
        
        public function controlEdit($row){
            
            printf('<input name="%s" id="'.$this->getControlName().'" value="%s" '.($this->field->isEditable()?'':'readonly="readonly"').' >',$this->getControlName(), $row[$this->field->name]);
        }
        
        public function startControlInTable(){
            print "<td>";  
        }

        public function finishControlInTable(){
            print "</td>";  
        }

        public function controlFilter($values){
            //print_r ($values);
            printf('<input name="%s" value="%s" />',$this->getControlName(), $values[$this->getControlName()]);
        }
        
        public function view($row){            
            print '&nbsp'.$row[$this->field->name];
            if (!isset($row[$this->field->name]) || $row[$this->field->name]=='') print('&nbsp');
        }

        public function viewValue(){
            print '&nbsp'.$this->value;

        }
        
        public function sanitize($value){

            if ($this->opt_current_user_id)
               return $_SESSION['user_id'];
            if ($this->opt_current_date)               
                return date("Y-m-d H:i:s");

            $value = preg_replace('/\</u','&lt', $value);
            $value = preg_replace('/\>/u','&gt', $value);          
            return $value;
        }

	    public function sanitizeAll($inputparams){
	        //error_log("satinizeAll");
        	return $this->sanitize($inputparams[$this->getControlName()]);
    	}
        
        public function validate($value){
            return True;
        }
        
        public function setValue($value){
            $this->value = $value;
        }
        
        public function restoreFilter($savedValue){
            return $savedValue;
        }
        
        public function restoreFilterAll($filterparams){
            $name = $this->getControlName();
            
            if (!empty($filterparams[$name]))
                return array($name => $this->restoreFilter($filterparams[$name]));
            else
                return null;
        }
        
        public function getCondition($filter_values){
            return $this->getExactCondition($filter_values);
        }
        
        public function getExactCondition($filter_values){
//            print "<br/>";
            //print "getExactCondition {$this->field->name}<br/>";
//            print_r ($filter_values);
//            print ($this->getControlName());
//            print "<br/>";
            if (isset($filter_values[$this->getControlName()]))
               return $this->field->name.'=:'.$this->getControlName();
            else
               return null;
        }
           
        public function getValueFromRow($row){
            return $row[$this->field->name];
        }
        
        public function startControl(){
            print "<div>\n";
            print '<label>'.$this->field->display_name.'</label>';
        }
        public function finishControl(){
            print "</div>";
        }
        
        public function showHeader($sortable = false){
            print "<th>";
            if ($sortable)
                print hlink(call_keep($this->module->metadata->table_name, '', array("sort_key" => $this->field->name)),$this->field->display_name);
            else
                print $this->field->display_name;    
            print "</th>";

        }
        
        public function viewInTable($row){
            print "<td>";
            $this->view($row);
            print "</td>";
        }
        
        public function getPhysicalFieldNames(){
            return array($this->field->name);
        }
        
        public function ExtractParams($args){
            if(array_key_exists($args,$this->field->name))
            {
            return array($this->field->name => $args[$this->field->name]);
            }
            else return null;   
        }
    }
?>