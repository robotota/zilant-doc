<?php

function prepare_params($params){
 $result = "";
 if ($params)
 	 foreach($params as $key=>$value)
 	     $result .= "&$key=$value";
 return $result;
} 

abstract class URLTestCase extends PHPUnit_Framework_TestCase{
    public $response = "";
    
    public abstract function getAddress();
    public function call_get($module, $action = '', $params = null){
        $cookieFile = 'cookie.txt';
        touch($cookieFile);
        $address =  $this->getAddress();
        $ch = curl_init("http://$address/index.php?module=$module&action=$action".prepare_params($params));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);
        curl_setopt($ch, CURLOPT_HEADER, True);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, True);
        //curl_setopt($ch, CURLOPT_POST, True);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, array('login'=>'admin', 'password'=>'password'));
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);    

        $output=curl_exec($ch);
        curl_close($ch);
        $this->response = $output;
        return $output;        
    }

    public function call_post($module, $action, $params){
        $cookieFile = 'cookie.txt';
        touch($cookieFile);
        $address =  $this->getAddress();
        $ch = curl_init("http://$address/index.php?module=$module&action=$action");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, True);
        curl_setopt($ch, CURLOPT_HEADER, True);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, True);
        curl_setopt($ch, CURLOPT_POST, True);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);    

        $output=curl_exec($ch);
        curl_close($ch);
        $this->response = $output;
        return $output;       
    }        
    
    // assertions
    public function isComplete(){
        
        $this->assertEquals(preg_match("/\<\/html\>/u", $this->response), 1, "Page isn't complete");
    }

    public function has($what){
        $this->assertEquals(preg_match($what, $this->response), 1, "$what not found");
    }
    
    public function hasNo($what){
        $this->assertEquals(preg_match($what, $this->response), 0, "$what is found");
    }

    public function countUp($what, $where = null){
        if ($where==null)
            $where =& $this->response;
        return preg_match_all($what, $where, $result);
    }
    public function hasInputForm(){
        $this->has('|<form class="input_form"|u');
        $this->has('|</form>|u');
    }
    
    // commands 
    public function login($login, $password){
        $this->call_post("Auth", "login", array('login'=>$login, 'password'=>$password));

    }
    public function logout(){
        $this->call_get("Auth", "logout");
    }

}

abstract class NullTestCase extends URLTestCase{    
    public function testBadLogin(){
        $this->logout();
        $this->login('admin', 'ololo');
        $this->isComplete();
        $this->has("/Неверный логин или пароль/u");
        $this->hasNo("/Выход/u");
         $this->has("/edtLogin/");
    }

    /**
     @depends testBadLogin
    */
    
    public function testLogin(){
        $this->login("admin", "password");
        $this->isComplete();
        $this->has("/Выход/u");
        $this->has("/Пользователи/u");
    }

    

    /**
     @depends testLogin
    */
    public function testGoUsers(){
        $this->call_get("users");
        $this->isComplete();
        $this->has("/username/u");
    }
    
    /**
    @depends testGoUsers
    */
    public function testAddUser(){
        $this->call_get("users", "", array("mode"=>"new"));
        $this->isComplete();
        $this->hasInputForm();
        $this->has('|<input name="username"|u');
        $this->call_post("users", "add", array('username'=>"addeduser", "name"=>"Added User", "password"=>"PassWord", "email" => "noname@ororor.com"));
        $this->isComplete();
        $this->has("/addeduser/u");
        $this->has("/Added User/u");
        $this->has("/noname@ororor.com/u");
    }

    /**
     @depends testAddUser
    */ 
    public function testReloginAsUser(){
        $this->call_get("Auth", "logout");
        $this->isComplete();
        $this->call_post("Auth", "login", array("login"=>"addeduser", "password"=>"PassWord"));
        $this->isComplete();
        $this->hasNo("|Неверный логин или пароль|u");

    	$this->call_get("Auth", "logout");
        $this->call_post("Auth", "login", array("login"=>"admin", "password"=>"password"));
        $this->isComplete();
        
    }

    /**
     @depends testReloginAsUser
    */ 
    public function testDelUser(){
        
        $this->call_get("users");
        preg_match('|<td>(?P<id>\d+)</td><td>addeduser</td>|', $this->response, $result);
        $this->assertTrue(is_numeric($result['id']));
        $id = $result['id'];
        $this->call_get("users", "", array("mode"=>"delete", "id" => $id));
        $this->isComplete();
        $this->has("|Действительно|u");
        $this->call_get("users", "delete", array("id" => $id));
        $this->isComplete();
        $this->hasNo("|addeduser|");
    }
    /**
     @depends testDelUser
    */ 
    public function testLogout(){
        $this->logout();
        $this->has("/Войти/u");

    }
}

?>
