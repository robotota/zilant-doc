<?php
// @class рендеринг таблиц, рендеринг форм, обработка действий. Перенаправление работы в Table
class TableHandler extends Base{
    public function __construct($table_name, $parent_module = '', $parent_id = '', $filter = null){
        parent::__construct();

        $this->parent_module = $parent_module;
        $this->parent_id = $parent_id;

        $this->filter = nvl($filter, array());

        $this->metadata = Table::getTable($table_name, $this);
        
        $this->__prefix = "";
        
        $this->__included_to = null;
    }

    // до getRootTableName унести в table
    public function getPrefix(){
        return $this->__prefix;
    }
    
    public function setPrefix($prefix){
        $this->__prefix = $prefix;
    }
    
    public function getIncludedTo(){
        return $this->__included_to;
    }
    
    public function setIncludedTo($included_to){
        $this->__included_to = $included_to;
    }               

    
    public function getRootTableName(){
        $handler = $this;
        while ($handler->getIncludedTo() != null)
            $handler = $handler->getIncludedTo();
        
        return $handler->metadata->table_name;
    }
    // перекрываемая функция
    // @short Действия над каждым объектом таблицы
    protected function itemActions(){
        global $auth;
        $actions =  array("Открыть" => array("action" => "",
                                              "mode"=>"edit",
                                              "call"=>"sub",
                                              "class" => "edit",
                                              "edit" => false));
        if ($auth->is_admin())
                     $actions["Удалить"] = array("action" => "",
                                        "mode"=>"delete",
                                        "call"=>"sub",
                                        "class" => "delete",
                                        "edit" => true);
        return $actions;
    }

	protected function innerItemActions(){
	    return array();
	}
	
	// @short список действий над всей таблицей
    protected function tableActions(){
        return array("Добавить новую запись" => array("action" => "",
                                              "mode"=>"new",
                                              "call"=>"sub",
                                              "class" => "button",
                                              "edit" => true));
    
    }
    
    protected function showActions($actions, $params, $edit, $wrap = 'span'){
        foreach($actions as $caption => $description){
            if (!$description['edit'] || $edit){
		        if (empty($description['mode']))
		            unset($params['mode']);
		        else
		            $params['mode'] = $description['mode'];    
		        if ($description['call'] == "sub")
		            $href = call_sub($this->metadata->table_name, $this->parent_module, $this->parent_id, $description['action'], $params);
		        else   
		            $href = call_keep($this->metadata->table_name, $description['action'], $params);
				$hlink = hlink($href, $caption);                
		            print "<$wrap class={$description['class']}>$hlink</$wrap>";
			}
        }
    
    }
	// @short рендер списка действия над объектами
    protected function showItemActions($id, $edit /* TODO rights instead */){
		$params = array("id"=>$id);
        $this->showActions($this->itemActions(), $params, $edit, 'td');
    }
    
    // @short рендеринг действий над всей таблицей
    protected function showTableActions($edit){
        $this->showActions($this->tableActions(), array(), $edit);
    }
    
	// @short рендер списка действия над объектами
    protected function showInnerItemActions($id, $edit = True /* TODO rights instead */){
		$params = array("id"=>$id);
        $this->showActions($this->innerItemActions(), $params, $edit);
    }


    public function showInnerFilter(){
        foreach($this->metadata->visibleGridFields() as $field){ 
            $processor = getFieldProcessorInstance($field, $this);
            $root_table_name = $this->getRootTableName();    
            $values = @$_SESSION['filter_params'][$root_table_name];
            $processor->startControlInTable();
            $processor->controlFilter($values);
            $processor->finishControlInTable();
            foreach($this->showAdditionalTableHeader($field) as $add)
                print "<td></td>";
        }
    }
    // @short рендеринг фильтра
    
    protected function showFilter(){
        print "<tr class=filter> <form method=post action='".call_keep($this->metadata->table_name, 'filter')."'>";
        $this->showInnerFilter();        
        print "<td> <input type=submit value='Поиск'> <input type='button' value='Очистить' onclick='javascript:clearForm(this.form)'> </td>";
        print "</form><td></td></tr>";
    }

    
	// @short рендеринг подтаблиц
    protected function showSubTables($id){
        print ("<h1> Ссылающиеся данные</h1>");
        foreach ($this->metadata->subTables() as $subTable){
            $view = new TableHandler($subTable['table_name'],$this->metadata->table_name, $id, array('exact'=>array($subTable['field_name'] => $id)));
            $view->showTable(true);
        }    
    }
    

    
    // @short заголовки вычислимого поля
    protected function showAdditionalTableHeader($after){
       return array();    
    }
    
    
    public function showInnerTableHeader(){
        foreach ($this->metadata->visibleGridFields() as $field){
            
            $sortable = empty($this->parent_module);
            $fieldProcessor = getFieldProcessorInstance($field, $this);
            $fieldProcessor->showHeader($sortable);            
            $adds = $this->showAdditionalTableHeader($field);
            foreach ($adds as $add)
                print "<th>$add</th>";
        };

    }
    // @short рендеринг заголовка таблицы
    protected function showTableHeader(){
        print "<tr>";
        $this->showInnerTableHeader();
        print "<th></th></tr>\n"; // здесь задается ширина столбца с кнопками "поиск очистить"
    }
    
        
    // @short значение в строке вычислимой колонки (ПОЛЕ, СТРОКА ДАННЫХ)
    protected function showAdditionalTableColumn($field, $row){
    
    }

    public function getUserFilter($values){
        $filter = array();
        foreach ($this->metadata->fields as $field){        
                $processor = getFieldProcessorInstance($field, $this);    
                $fieldFilter = $processor->restoreFilterAll($values);
                //print "fieldFilter:";
                //print_r($fieldFilter);
                if ($fieldFilter!=null)
                    $filter = array_merge($filter, $fieldFilter);
        }
        return $filter;
    }

    // @short заполнение фильтра из сессии
    protected function fillUserFilter(&$filter){
        if ($filter == null)
            $filter = array();
        $filter['user'] = $this->getUserFilter($_SESSION['filter_params'][$this->getRootTableName()]);
    }
	
	public function showRow($row){
     /*   foreach ($row as $key=>$value){ 
            $field = $this->metadata->fields[$key];
            if ($field->isGridVisible()){
                $fieldProcessor = getFieldProcessorInstance($field, $this);
                $fieldProcessor->viewInTable($row);
            }
            $this->showAdditionalTableColumn($field, $row);
        } //*/
       foreach ($this->metadata->fields as $field){ 
            if ($field->isGridVisible()){
                $fieldProcessor = getFieldProcessorInstance($field, $this);
                $fieldProcessor->viewInTable($row);
            }
            $this->showAdditionalTableColumn($field, $row);
        }
	}
	// @short рендеринг таблицы с действиями и подтаблицами (РЕДАКТИРОВАНИЕ, МЕТАДАННЫЕ)
    function showTable($edit = True){
        if (empty($this->parent_module))
            $this->fillUserFilter($this->filter);    
        $order = nvl_get('sort_key', 'id');

        $count = $this->metadata->getCount($this->filter);
        print "<div>";
        print"<h2> {$this->metadata->display_name} ($count) </h2>";

        print "<div class='tableActions'>";        
        $this->showTableActions($edit);
        print "</div>";

    	print '<table class="grid">';
    // делаем заголовок

        $this->showTableHeader(); 
		if (empty($this->parent_module)){
		    $skip = $_GET['skip'] * 10;
            $this->showFilter(); 
		    }
		else
		    $skip = null;
		$objects = $this->metadata->getObjects($this->filter, $order, $skip);
        
        $stated = $this->metadata->hasStatus();
        if ($stated){
            $tablename = $this->metadata->fields['status_id']->foreign_table_name;
            $statestable = Table::getTable($tablename, $this);
            $rows = $statestable->getObjects();
            $states = array();
            foreach ($rows as $row){
                $states[$row['id']] = $row['code'];
            }
        }

        while ($row = $objects->fetch()){
            if ($row['status_id'] != null)            
            print "<tr class='status_{$states[$row['status_id']]}'>";
            else
            print "<tr>"; 
            $this->showRow($row);
            $this->showItemActions($row['id'], $edit);
            print "</tr>\n";
        }

        print "</table>\n";
        
        print "<div>";
        
        if (empty($this->parent_module)){
        $count = $this->metadata->getCount($this->filter);
        $page_count =  floor($count / 10);
        
        if ($count % 10 > 0)
           $page_count++; 
        for ($i = 0; $i < $page_count ; $i++){
              $page_number = $i + 1;
              if ($_GET['skip'] == $i)
                  print "<span class='selectedpage'>$page_number</span>";
                  
              else    
                  print hlink(call_keep($this->metadata->table_name, "", array("skip"=>$i)), $page_number, "gridpage");
          }
        }  
        print "</div>";  
        print "</div>\n";
    }

    // @short значение для поля по умолчанию
    function getDefaultValue($fieldname){
        if ($fieldname == "date_created")
            return date("Y-m-d H:i:s");
        else if ($fieldname=="created_by_id")
            return $_SESSION['user_id'];
        else if ($fieldname == "date_modified")
            return date("Y-m-d H:i:s");
        else if ($fieldname=="modified_by_id")
            return $_SESSION['user_id'];

        return null;
    }
    
    
    public function generateNewEditFields(){
        $stack = getIdStack();
        foreach ($this->metadata->visibleFields() as $field){
            $fieldProcessor = getFieldProcessorInstance($field, $this);
            $fieldProcessor->startControl();            
            if (($field->type == 'reference' or $field->type == 'dictionary' ) and $field->foreign_table_name == $stack->getLastModule())
                $fieldProcessor->setValue($stack->getLastId());
            else
                $fieldProcessor->setValue($this->getDefaultValue($field->name));
            
            //if ($field->isEditable())
                $fieldProcessor->controlNew();
            
            //    $fieldProcessor->viewValue();
    

            $fieldProcessor->finishControl();
        };

    }
    // @short рендеринг формы нового объекта
    function newObjectForm(){

	        
	    print '<form class="input_form" enctype="multipart/form-data" method="post" action="'.call_keep($this->metadata->table_name, "add").'">';
        $this->generateNewEditFields();        
        print "<div>";
        print "<input type='submit' value='Добавить и вернуться'>";
        print "<input type='submit' name='stay' value='Добавить и остаться'>";
        print "</div>";    
        print "</form>";
    }
    
    function generateEditFields($id){
        $object = $this->metadata->loadObject($id);
        foreach ($this->metadata->visibleFields() as $field){
                    $fieldProcessor = getFieldProcessorInstance($field, $this);
                    $fieldProcessor->startControl();
                    
                    $fieldProcessor->controlEdit($object);
                    
                    $fieldProcessor->finishControl();
                }
    }
    // @short рендеринг формы редактирования объекта по ID (МЕТАДАННЫЕ, ID)
    function editObjectForm($id){

     	print '<form class="input_form" method="post" action="'.call_keep($this->metadata->table_name, "update").'">';
        $this->generateEditFields($id);
        print "<div><input type='submit' value='Сохранить изменения'></div>";
        print "</form>";
        
        print "<div class='actions'>";
        $this->showInnerItemActions($id);
        print "</div>";
        
        $this->showSubTables($id);
    }

    // @short рендеринг формы показа объекта по id (МЕТАДАННЫЕ, ID)
    function showObjectForm($id){

        global $auth;
	    
	    $object = $this->metadata->loadObject($id);
        
        if ($object){
         	print '<div>';
            foreach ($this->metadata->visibleFields() as $field){
                $fieldProcessor = getFieldProcessorInstance($field, $this);
                $fieldProcessor->startControl();
                $fieldProcessor->view($object);
                $fieldProcessor->finishControl();

            }
            print hlink(call_return($this->metadata->table_name), 'Вернуться назад');
            print "</div>";
        }

        $this->showSubTables($object['id']);
    }

	// @short рендеринг формы подтверждения удаления
    function showDeleteConfirmation($id){
        require "./core/forms/deleteConfirmation.php";
    }

	// @short диспетчер режимов
    function showContents(){
        $mode = "show";
        if (isset($_GET['mode']))
            $mode = _GET('mode');
        switch ($mode){
            case "show":
                $this->showTable();
                break;
            case "new":
                $this->newObjectForm();
                break;
            case "edit":
                $this->editObjectForm(_GET('id'));
                break;
            case "delete":
                $this->showDeleteConfirmation(_GET('id'));
        }
    }    
	
    // @short обработка команды filter - заполнение фильтра из post
    public function do_filter(){
       if (empty($_SESSION['filter_params']))
           $_SESSION['filter_params'] = array();
       $_SESSION['filter_params'][$this->metadata->table_name] = $_POST; // с этим что-то надо делать, это опасно
    }


	// @short обработка добавления объекта

    function postAdd(){
    }

    public function add($input_params){
        $params = $this->metadata->sanitize($input_params, $this);
        if (!$this->metadata->validate($params, $this)) // переделать на исключения?
            redirect(call_keep($this->metadata->table_name, '',array("mode"=>"new")));
        
        $newId = $this->metadata->addObject($params,$this);
        print("new id is $newId<br/>");
        return $newId;
}    
    function do_add(){
        $this->add($_POST);
		$this->postAdd();
        $params = array();
        
        if (isset($_POST['stay'])){
            $params['id'] = Connection::getConnection()->lastInsertId();
            $params['mode'] = 'edit';
            redirect(call_keep($this->metadata->table_name, '',$params));
        }
        else
            redirect(call_return($this->metadata->table_name));
    }
    // @short обработка удаления объекта
    function do_delete(){
        $this->metadata->deleteObject($_GET['id']);
        redirect(call_return($this->metadata->table_name));
    }          
  
    function postUpdate(){
    }
    
    // @short обработка обновления объекта
    function update($inputParams){
        $params = $this->metadata->sanitize($inputParams, $this);
        if (!$this->metadata->validate($params, $this))                
            redirect(call_keep($this->metadata->table_name, '',array("mode"=>"edit", "id"=>$params['id'])));

       $this->metadata->updateObject($params, $this);
     }   
    public function do_update(){
        $this->update($_POST);
        $this->postUpdate();
        redirect(call_return($this->metadata->table_name));
    }  
    
}
?>
