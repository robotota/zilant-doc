<?php
    class FPboolean extends FPDefault{

        public function controlNew(){
            print ("<input type='checkbox' name=\"{$this->field->name}\" /> <br/>");
        }
        
        public function controlEdit($row){

            print ("<input type='checkbox' name=\"{$this->field->name}\" ".($row[$this->field->name]?"checked='checked'":'')."/> <br/>");
        }
        public function view($row){
            print "<div style='text-align:center'>".($row[$this->field->name]?'<img src="images/check.png" alt="V">':"&nbsp")."</div>";
        }
        
        public function controlFilter($value){
            $selected_any = empty($value)?'selected':'';
            $selected_yes = ($value=='yes')?'selected':'';
            $selected_no = ($value=='no')?'selected':'';            

            print "<select name='{$this->getControlName()}'><option value='' $selected_any></option> <option value='no' $selected_no> нет </option> <option value='yes' $selected_yes> да </option> </select>";
        }

        public function restoreFilter($savedValue){
            return $savedValue == 'yes'?1:0;
        }
        
        public function getFieldValueFromRow($row){
            return isset($row[$this->field->name]);
        }
        
        public function sanitize($value){
        if ($value == 'on')
            return 1;
        else
            return 0;
        }
        
    }

?>