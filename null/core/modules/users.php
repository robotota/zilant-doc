<?php
class users extends TableHandler{

    function __construct(){
        parent::__construct('users');
    }    
    
    
    function sendMail($data){
       global $SEND_MAIL, $PROJECT_CAPTION, $PROJECT_MAIL, $PROJECT_URL;

       if ($SEND_MAIL){
       $NAME = $data['name'];
       $USERNAME = $data['username'];
       $PASSWORD = $data['password'];
       $message = 
       "Здравствуйте, $NAME!
Вами либо кем-то еще был изменен пароль к учетной записи $USERNAME в системе
$PROJECT_CAPTION.

Адрес  : $PROJECT_URL
Логин  : $USERNAME
Пароль : $PASSWORD";
	   $headers =  "From: {$PROJECT_MAIL}\r\n";
	   $headers .= "MIME-Version: 1.0\r\n";
       $headers .= "Content-Type: text/plain;charset=utf-8";
	   mail($data['email'], 'Обновление личных данных', $message, $headers);
       }
}


    function postAdd(){
        global $auth;
        if ($auth->is_admin()){
            $this->sendMail($_POST);
    	}
	}
    function do_add(){
        global $auth;
        if ($auth->is_admin()){
            parent::do_add();
        }     
    }

  	function postUpdate(){
        global $auth;
        if ($auth->is_admin()){
            $this->sendMail($_POST);
        }  
  	}

    function do_update(){
        global $auth;
        if ($auth->is_admin()){
            parent::do_update();
        }  
    }
  
    function do_delete(){
        global $auth;
        if ($auth->is_admin()){
            parent::do_delete();
        }  
    }

    public function showTable($edit = True){
        global $auth;
        parent::showTable($auth->is_logged_in() and $auth->is_admin());
    }

    function showContents(){
        global $auth;
        if (!$auth->is_admin() and _GET('mode') == 'edit')
            $this->showObjectForm(_GET('id'));
        else
            parent::showContents();
    }
  
}
?>