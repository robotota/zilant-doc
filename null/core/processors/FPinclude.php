<?php

class FPinclude extends FPDefault{
    function __construct($field, $module){
        parent::__construct($field, $module);        
        $includedModuleName = $this->field->foreign_table_name;
        $this->handler = new TableHandler($includedModuleName);
        $this->handler->setPrefix($this->module->getPrefix().$this->field->name."_");
        $this->handler->setIncludedTo($this->module);
    }

    public function controlNew(){
        $this->handler->generateNewEditFields();    
    }
        
    public function controlEdit($row){
        $this->handler->generateEditFields($row[$this->getControlName()]);    
        
    }    

    public function sanitizeAll($inputParams){
        // here we actually INSERT or UPDATE a row, depending on the ID field state
        if (isset($inputParams[$this->getControlName()."_id"])){
            // _id нашли, инициируем апдейт            
            $this->handler->update($inputParams);            
            return null; // $inputParams[$this->getControlName()];
        }
        else{
            //_id не нашли, инициируем создание
            return $this->handler->add($inputParams);       
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

    public function showHeader($sortable = false){
        $this->handler->showInnerTableHeader();       
    }
    
    public function viewInTable($row){
        $id = $row[$this->field->name];
        $row2 = $this->handler->metadata->loadObject($id);
        $this->handler->showRow($row2);   
    }

    public function controlFilter($value){
        $this->handler->showInnerFilter();
    }    
    
    public function getCondition($filter_values){    
        $conditions = implode(' and ', $this->handler->metadata->makeUserConditions($filter_values));
        
        if (!empty($conditions))
            return $this->getControlName()." in (select id from {$this->field->foreign_table_name} where $conditions)";
        else
            return null;
        
    }
    
    public function restoreFilterAll($filterparams){
        $filters = $this->handler->getUserFilter($filterparams);
        return $filters;
    }                        

}
?>