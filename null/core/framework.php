<?php
function nvl(&$var, $default){
    
    if (!empty($var))
        $tmp = $var;
    else
        $tmp = $default;
    return $tmp;     
}

class IdStack{

    private $data;
    const STACK_DIVISOR = '/';
    public function __construct($stack_data){
        //$this->parent::__construct();
        $this->data = explode(self::STACK_DIVISOR, $stack_data);
    }
    
    public function push($modulename, $id){
        $this->data[] = "$modulename:$id";
    }
    public function pop(){
        return array_pop($this->data);
    }
    public function value(){
        return implode(self::STACK_DIVISOR, $this->data);
    }
    
    public function peek(){
        return end($this->data);
    }
	
	public function getLastModule(){
  	    list($module, $parent_id) = explode(":", $this->peek());
   	    return $module;
	}
	
	public function getLastId(){
  	    list($module, $parent_id) = explode(":", $this->peek());
   	    return $parent_id;
	}
}

function getIdStack(){
  if (empty($_GET['stack'])){
      $_GET['stack'] = '';
  }
  return new IdStack($_GET['stack']);
}


function call($module, $action = '', $params = null){
    $address = "?module=$module";
    if (!empty($action))
        $address .= "&action=$action";
    if (!empty($params)){
        foreach ($params as $key=>$value)
            if (!empty($value))
                $address .= "&$key=$value";
    }
    return $address;
}


function call_keep($module, $action = '', $params = null){
        $params_new = $params;
        if (empty($params_new))
            $params_new = array();
        
        $stack = getIdStack();
        $params_new['stack'] = $stack->value();
        
        return call($module, $action, $params_new);
}

function call_return($module){
        $params = array();
        $stack = getIdStack();
        $pop = $stack->pop();
        if (!empty($pop)){
            list($module, $id) = explode(':', $pop);
            if (!empty($id)){
                $params['id'] = $id;
                $params['mode'] = 'edit';
            }    
            $params['stack'] = $stack->value();
        }    
	    return call($module, '', $params);
}


function call_sub($module, $from, $from_id, $action = '', $params = null){
        $params_new = $params;
        if (empty($params_new))
            $params_new = array();
        if ($from != ''){
           $stack = getIdStack();
           $stack->push($from, $from_id);
           $params_new['stack'] = $stack->value();
        }    
        return call($module, $action, $params_new);
}

function call_ex($module, $action, $params){
    return "?module=$module&action=$action&$params";
}

function redirect($address){
   header("Location: $address", True, 303);
   exit;
}
function hlink($ref, $text, $class = ''){
    $st = empty($class)?'':" class=\"$class\"";
    return "<a href='$ref' $st> $text </a>";
}

function _GET($name){
    //return mysql_real_escape_string($_GET[$name]);
    return $_GET[$name];
}

function _POST($name){
    //return mysql_real_escape_string($_POST[$name]);
    return $_POST[$name];    
}

function __autoload($name){
// пытаемся найти файл в modules
if (file_exists("./modules/$name.php"))
    require "./modules/$name.php";
elseif (file_exists("./core/processors/$name.php"))
    require "./core/processors/$name.php";
elseif (file_exists("./core/modules/$name.php"))
    require "./core/modules/$name.php";
elseif (file_exists("./core/classes/$name.php"))
    require "./core/classes/$name.php";
elseif (file_exists("./core/lib/$name.php"))
    require "./core/lib/$name.php";
    
};

function getModuleInstance($name){

if (file_exists("./modules/$name.php") or file_exists("./core/modules/$name.php"))
    return new $name();
else{

      $sth = Connection::getConnection()->prepare("select * from __display_names where table_name=:table_name");
      $result = $sth->execute(array('table_name'=>$name));
      $result_object = null;
      if ($sth->fetch())
          return new TableHandler($name);
      }
throw new Exception("Unknown module $name");      
}

function getFieldProcessorInstance($field, $module){
        $type = $field->type;
        $classname = 'FP'.$type;
        if ($field->name=='id')
            return new FPid($field, $module);
        elseif (file_exists("./core/processors/$classname.php"))
            return new $classname($field, $module);
        else
            return new FPDefault($field, $module);
    }
    
function nvl_get($key, $default){
  if (!empty($_GET[$key]))
      return $_GET[$key];
  else
      return $default;    
}

function getSqlParams($sql){
    preg_match_all("/\:((\w)+)/", $sql, $result);
    return $result[1];
}

function addMessage($message){
    if (!isset($_SESSION['messages']))
        $_SESSION['messages'] = array();
    $_SESSION['messages'][] = $message;
}

function array_change_key_name( $orig, $new, $array ) {
    $newkeys = array_keys($array);
    $newkeys[array_search($orig,$newkeys)]=$new;
   $res =  array_combine($newkeys,array_values($array));
  return $res;
}

function sendfile($realfilename, $filename){
if (file_exists($realfilename)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.$filename);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    ob_clean();
    flush();
    readfile($realfilename);
}
}

?>