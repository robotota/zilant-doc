<?php
class Table extends ArrayObject{
    public $module;
	public static function getTable($table_name, $module){
        
		//реконструкция tables по бд, загружаем все поля из mysql, а потом в этот список встраивались поля из __field_config, заменяя собой те, что возвращают для них getPhysicalFieldNames.

		$params = array('table_name'=>$table_name);

		//Пункт 1. смотрим человеческое название для данный нам таблицы
		$query="select * from __display_names where table_name=:table_name;";
		$sth = Connection::getConnection()->prepare($query);
		$sth->execute($params);
		$table = $sth->fetchObject('Table');
		$table->module= $module;
		if ($table == False){
		    $table = new Table();
		    $table->table_name = $table_name;
		    $table->display_name=$table_name;
		}  
		
        //Пункт 2.1 Загружаем все поля из mysql
        $query="SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.columns WHERE table_name = :table_name and table_schema=database();";

		$sth = Connection::getConnection()->prepare($query);
		$sth->execute($params);    
		$columns = $sth->fetchAll(PDO::FETCH_COLUMN);
		$fields = array();
		foreach($columns as $column_name){//создаем дефолтный список полей
		    $field = new Field();
		    $field->type = 'string';
		    $field->name = $column_name;
		    $field->display_name = $column_name;
		    $field->editable = 1;
		    $field->display = 1;
		    $field->display_in_grid = 1;
		    $field->foreign_table_name = '';
		    $field->foreign_name = 'name';
		  $fields[$column_name] = $field;
		}
		 
		//Пункт 2.2 Загружаем список полей из __field_config
		$query="select * from __field_config where table_name=:table_name;";
		$sth = Connection::getConnection()->prepare($query);
		$sth->execute($params);    
		$resultrows = $sth->fetchAll();
		foreach($resultrows as $resultrow){//для каждой записи инстанцируем фп, смотрим какие PhysicalFieldNames он вернет, удаляем соответствующие поля из $fields, добавляем соотв. поля.
		    $field = new Field();
		    $field->type = $resultrow['type'];
		    $field->name = $resultrow['name'];
		    $field->display_name = $resultrow['display_name'];
		    $field->editable = $resultrow['editable'];
		    $field->display = $resultrow['display'];
		    $field->display_in_grid = $resultrow['display_in_grid'];
		    $field->foreign_table_name = $resultrow['foreign_table_name'];
		    $field->foreign_name = $resultrow['foreign_name'];
            if ($field->foreign_name == "")
                $field->foreign_name = "name";
		    $field->options = $resultrow['options'];
		    $fpinstance = getFieldProcessorInstance($field, $module);
		    $physicalFields=$fpinstance->getPhysicalFieldNames();
		    $firstField=True;
		    
		    foreach($physicalFields as $physicalfield){//удаляем соотв. поля, первое вместо удаления перезаписываем
		        if ($firstField) {
		            $fields[$physicalfield] = $field;
		            $fields = array_change_key_name($physicalfield,$field->name,$fields);
		            //die(var_dump($fields));
		            $firstField = false;
		            }
		        else
		           {
		            if(array_key_exists($physicalfield,$fields)){
		                unset ($fields[$physicalfield]);
		            }
		        }
		        }
		//die(var_dump($fields)); //debug
		}
		
		//Пункт 3. Собираем обьект-типа-table
		
		$table->fields = $fields;

		return $table;
	}
    
    public function hasField($name){
        foreach ($this->fields as $field)
            if ($field->name == $name)
                return True;
        return False;
    }

    public function hasStatus(){
        return $this->hasField('status_id');
    }


	// @short список видимых полей (МЕТАДАННЫЕ)
    public function visibleFields(){
        $fields = array();
        foreach($this->fields as $field){
            if ($field->isVisible())
                $fields[] = $field; 
        }
        return $fields;
    }
    
    public function visibleGridFields(){
        $fields = array();
        foreach($this->fields as $field){
            if ($field->isGridVisible())
                $fields[] = $field; 
        }
        return $fields;        
    }

	// @short список видимых полей (МЕТАДАННЫЕ)
    public function editableFields(){
        global $auth;
        $fields = array();
        foreach($this->fields as $field){
            if ($field->editable || $auth->is_admin())
                $fields[] = $field; 
        }
        return $fields;
    }

  	// @short очистка входных параметров (ВВОД. МЕТАДАННЫЕ)
  	// к переносу в хендлер
    public function sanitize($input_params, $module){
        $output_params = array();
        foreach ($this->fields as $field){
            $processor = getFieldProcessorInstance($field, $module);
                $out = $processor->sanitizeAll($input_params);
                if (!is_null($out))
                {
                    if(is_array($out))
                    {
                        $output_params = array_merge($output_params,$out);
                    }
                    else
                    {
                        $output_params[$field->name] = $out;
                    }
                }
        }
        return $output_params;
    }

    // @short проверка входных параметров (ВВОД, МЕТАДАННЫЕ)
    //это вообще нужно?
    public function validate($input_params, $module){
        if ($module == null)
           print ("module is null");
        
        $result = true;
        foreach ($this->fields as $field){
            if (isset($input_params[$field->name])){
                $processor = getFieldProcessorInstance($field, $module);
                if (!$processor->validate($input_params[$field->name])){
                    addMessage("Поле {$field->display_name} заполнено неверно.");
                    $result = false;
                }    
            }
        }
        return $result;
    }


    function makeUserConditions($user_filter){
        $conditions = array();
        foreach($this->fields as $field){
            $processor = getFieldProcessorInstance($field, $this->module);
            $localConditions = $processor->getCondition($user_filter);
            if (!empty($localConditions))
                $conditions[] = $localConditions;
        }
        return $conditions;
    }
    
    function makeExactConditions($exact_filter){
        $conditions = array();
        foreach($this->fields as $field){
            $processor = getFieldProcessorInstance($field, $this->module);
            $localConditions = $processor->getExactCondition($exact_filter);
            
            if (!empty($localConditions))
                $conditions[] = $localConditions;
        }
        return $conditions;    
    }
    
    function makeCustomConditions($custom_filter){
        $conditions = array($custom_filter['sql']);
        return $conditions;    
    }


    function makeConditions($filter){
        if(isset($filter)){
		    // бежим по метаданным
		    $conditions = array();
            if(isset($filter['user'])){
                $conditions = array_merge($conditions, $this->makeUserConditions($filter['user']));
		    }
                    
	        if(isset($filter['exact'])){
                $conditions = array_merge($conditions, $this->makeExactConditions($filter['exact']));
	        }
	        
	        if (isset($filter['custom'])){
	            $conditions = array_merge($conditions, $this->makeCustomConditions($filter['custom']));
	        }
		    return implode(' and ', $conditions);
		}
		return '';
    }
    
    function makeSelect($filter = null, $order = null, $skip = null){

		// делаем запрос
		
		$field_names = array();    
		foreach ($this->fields as $field)
		    {
		    $fpinstance = getFieldProcessorInstance($field, $this->module);
		    $physicalFields=$fpinstance->getPhysicalFieldNames();
		    $field_names= array_merge($field_names, $physicalFields);
		    //die(var_dump($field_names, $physicalFields));
		    }
		
		$field_list = implode(', ', $field_names);
	
		$query="select $field_list from {$this->table_name}";

        $where_clause = $this->makeConditions($filter);

		if (!empty($where_clause))
		    $query .= ' where '.$where_clause;
		if (!empty($order))
		    $query .= ' order by '.$order;
		
		if (!empty($skip) || $skip == '0')
		    $query .= ' limit '.$skip.',10';    
		
		return $query;
	}

	// @short загрузка объекта (МЕТАДАННЫЕ, ID)
    function loadObject($filter_or_id){
        if (is_array($filter_or_id))
            $filter = $filter_or_id;    
        else
			$filter = array('exact' => array($this->module->getPrefix().'id'=>$filter_or_id));
	            
        $sql = $this->makeSelect($filter);
        $sth = Connection::getConnection()->prepare($sql.' limit 1');

        $result = $sth->execute($this->flattenFilter($filter));
        return $sth->fetch();
    }
    
    function loadObjectValue($expression, $id){
    	$sql = "select ($expression) as value from {$this->table_name} where id = :id";
        $sth = Connection::getConnection()->prepare($sql);

		$result = $sth->execute(array('id' => $id));
		$result = $sth->fetch();
		return $result['value'];    	
    }

	public function subTables(){

		$sql = "select table_name,  name field_name from __field_config where (type = 'reference' or type = 'dictionary') and foreign_table_name=:table_name;";
		$sth = Connection::getConnection()->prepare($sql);
		$result = $sth->execute(array("table_name"=>$this->table_name));
		return $sth->fetchAll();
	}

	function updateObject($args, $module){
		global $auth;
		$fields = array();
		$values = $args;
		$keys = array_diff(array_keys($args), array('id'));
		/*
		$values = array();
		$arg_fields = array_keys($args);
		$real_fields = array_keys($this->fields);
		$updated_fields = array_intersect($arg_fields, $real_fields);
		$updated_fields = array_diff($updated_fields, array('id'));
	    
	    $fullparams=array();
        foreach($this->fields as $field){   
            $processor = getFieldProcessorInstance($field, $module);
            $params = $processor->ExtractParams($args);
            if (isset($params)){
                $fullparams = array_merge($fullparams, $params);
                }
        }  
        $keys = array_keys($fullparams); */
        $sqlset = array();
        foreach($keys as $key)
            $sqlset[] = $key ."=:".$key;
            
        		
        $sql = "update {$this->table_name} set ".implode(', ', $sqlset)." where id = :id;";
        //die(var_dump($values));
		//$values['id'] = $args['id'];
		//$values = array_merge($values,$fullparams);
		$sth = Connection::getConnection()->prepare($sql);
		$sth->execute($values);
	}

	function addObject($args, $module){

		//$fields = array();
		//$values = array();
		$values = $args;
		if (array_key_exists('id',$values))
		    {
		    unset($values['id']);
		    }
		$keys = array_diff(array_keys($args), array('id'));
		/*
		$arg_fields = array_keys($args);
		$real_fields = array_keys($this->fields);
		$inserted_fields = array_intersect($arg_fields, $real_fields);
		$inserted_fields = array_diff($inserted_fields, array('id'));

		foreach ($inserted_fields as $field_name){
	        // все поля, что есть в args, должны попасть в $fields и в $values
            $field = $this->fields[$field_name];		        
            $fields[] = $field->name." = :".$field->name;
            $processor = getFieldProcessorInstance($field, $module);
            $values[$field->name] = $processor->getValueFromRow($args);  
		}
        
        
		$sql = "insert into {$this->table_name} set ".implode(', ', $fields).";";
		//*/
		$sqlset = array();
        foreach($keys as $key)
            $sqlset[] = $key ."=:".$key;
            
        $sql = "insert into {$this->table_name} set ".implode(', ', $sqlset).";";
        
        //die(var_dump($values));
		$sth = Connection::getConnection()->prepare($sql);
		$sth->execute($values);
		return Connection::getConnection()->lastInsertId();
	}
	

	function deleteObject($id){

		$sql = "delete from {$this->table_name} where id= :id";
		$sth = Connection::getConnection()->prepare($sql);
		$sth->execute(array('id'=> $id));	
	}
	
	function flattenFilter($filter){
	    $exact = array();
	    if (isset($filter['exact']))
	        $exact = $filter['exact'];
	    $user = array();
	    if (isset($filter['user']))
	        $user = $filter['user'];
	    $custom = array();
	    if (isset($filter['custom']))
	        $custom = $filter['custom']['params'];
	        
	    return array_merge ($exact, $user, $custom);    
	        
	}
	function getObjects($filter = null, $order = null, $skip = null){
		$sql = $this->makeSelect($filter, $order, $skip);
        $sth = Connection::getConnection()->prepare($sql);
        $sth->execute($this->flattenFilter($filter));
        
        return $sth;
	}
	
	function getCount($filter = null){
	    $sql = $this->makeSelect($filter);
		$sth = Connection::getConnection()->prepare("select count(*) as cnt from ($sql) data");
        $flattened = $this->flattenFilter($filter);
		$result = $sth->execute($flattened);
		$row = $sth->fetch();
		return $row['cnt'];
	}

}

?>
