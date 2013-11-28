<?php

    class FPmultiinclude extends FPDefault{
    
    function __construct($field, $module){
        parent::__construct($field, $module);        
    }
    
    function initHandler($includedModuleName){
        //$includedModuleName = $this->field->foreign_table_name;
        $handler = new TableHandler($includedModuleName);
        $handler->setPrefix($this->module->getPrefix()."_".$this->field->name."_".$includedModuleName."_");
        
        $handler->setIncludedTo($this->module);
        return $handler;
    }
    // getTableList ? 
    function getOptions(){
        //забираем из метадаты options, и парсим его в массив считая разделителем запятую
        return explode (",", $this->module->metadata->fields[$this->field->name]->options);
    }
    
   function getIdsForJs($ids){
        $result = array();
        foreach ($ids as $id ){
            $result[] = '#'.$this->getControlName()."_".$id;
        }    
        return implode(',', $result);    
    }
    public function controlNew(){
        $tablesList = $this->getOptions();
        $select_id = $this->getControlName()."_type";
        print "<select name='$select_id' id='$select_id'>";
        
        foreach($tablesList as $table)
            {
                $handler = $this->initHandler($table);
                print "<option value = '".$table."'> ".$handler->metadata->display_name."</option>";
            }
        print "</select>";

        //die(var_dump($tablesList));
        
        
        foreach($tablesList as $table)
            {
                $handler = $this->initHandler($table);
                print "<div id=".$this->getControlName()."_".$handler->metadata->table_name." >";
                //print "<fieldset>";
                //print "<legend>{$handler->metadata->display_name}</legend>";
                $handler->generateNewEditFields();
                print "</div>";
                //print "</fieldset>";
            }
        $ids = $this->getIdsForJs($tablesList);
        $controlName = $this->getControlName();
        print "<script> var f= function () { var id= '#'+'$controlName'+'_'+$(this).val(); $('$ids').hide(); $(id).show();};";
        print "         select = $('#$select_id');";
        print "         select.on('change', f);";
        print "         select.change(); </script>";
            
    }
        
    public function controlEdit($row){
        $tablesList = $this->getOptions();
        print "<input type=hidden name= ".$this->getControlName()."_type value = ".$row[$this->field->name."_outer_type"].">";
      /*  
         // генерируем селект для выбора типа
        print "<select name=".$this->getControlName()."_type>";
        foreach($tablesList as $table)
            {
            $handler = $this->initHandler($table);
            if($table == $row[$this->field->name."_outer_type"])
                {
                print "<option value = '".$table."' selected> ".$handler->metadata->display_name."</option>";
                }
            else
                {
                print "<option value = '".$table."'> ".$handler->metadata->display_name."</option>";
                }
            }
        print "</select>"; //*/
        foreach($tablesList as $table) // генерируем поля, new для невыбранного, edit для выбранного
            {
                $handler = $this->initHandler($table);
                if($table == $row[$this->field->name."_outer_type"])
                    {
                    print "<div id=".$handler->metadata->table_name.">";
                   
                    $handler->generateEditFields($row[$this->getControlName()."_outer_id"]);
                    print "</div>";   
                    }
               /* else
                    {
                    $handler->generateNewEditFields();
                    } //*/
                
                
            }
         
        
    }    

    public function sanitizeAll($inputParams){

        // here we actually INSERT or UPDATE a row, depending on the ID field state
        // сразу инстанцируем хендлер для выбранной таблицы
        $table = $inputParams[$this->getControlName()."_type"];
        $handler = $handler = $this->initHandler($table); 
        $indexname = "_".$this->getControlName()."_".$inputParams[$this->getControlName()."_type"]."_id";
        //die(var_dump($inputParams).$indexname);   
        if (isset($inputParams[$indexname]))
        {
            
            $handler->update($inputParams);
            return null;
        }
        else
        {
            $result =  array($this->field->name."_outer_type" => $table,$this->field->name."_outer_id" => $handler->add($inputParams));
            return $result;
        }
    }
    
    public function startControl(){
        print "<div>";
        print "<fieldset>";
        print "<legend>{$this->field->display_name}</legend>";
    }
    
    public function finishControl(){
        print "</fieldset>";
        parent::finishControl();
    }

    public function startControlInTable(){
        // do nothing
    }

    public function finishControlInTable(){
        // do nothing
    }    

    public function viewInTable($row){
        $id = $row[$this->field->name."_outer_id"];
        $table = $row[$this->field->name."_outer_type"];
        $handler = $this->initHandler($table);
        
        $row2 =$handler->metadata->loadObject($id);

        //$handler->showRow($row2);   
        //print $handler->getName($id);
        print "<td>";
        print $row2['name'];
        print "</td>";
    }

    public function controlFilter($value){
        //$this->handler->showInnerFilter();
        print "<td></td>";
    }    
    
    public function getCondition($filter_values){    
        /*$conditions = implode(' and ', $this->handler->metadata->makeUserConditions($filter_values));
        
        if (!empty($conditions))
            return $this->getControlName()." in (select id from {$this->field->foreign_table_name} where $conditions)";
        else
            return null;
        */
        return null;
      
    }
    
    public function restoreFilterAll($filterparams){
        /*$filters = $this->handler->getUserFilter($filterparams);
        return $filters;*/
        return null;
    }
    
    public function getPhysicalFieldNames(){
        return array($this->field->name."_outer_type",$this->field->name."_outer_id");
    }
    
    
    public function showHeader($sortable = false){
            print "<th>";
            //if ($sortable)
            //    print hlink(call_keep($this->module->metadata->table_name, '', array("sort_key" => $this->field->name)),$this->field->display_name);
            //else
                print $this->field->display_name;    
            print "</th>";

        }
  
   
}

?>