<?php

class Auth extends Base{
  function __construct(){
      parent::__construct();
      $this->isNewTicketAvailable = false;
  }
  
  function showContents(){      
    require('./core/forms/login.php');  
  }
  
    function is_logged_in(){
        // смотрим, а не чо нам говорит сессия
        $logged_in = isset($_SESSION['logged_in']) and $_SESSION['logged_in'];
        if (!$logged_in){
            // попробуем залогиниться по хешу, а вдруг?
            if ($_COOKIE['user_hash']){
                // мы не можем использовать Table
                $sql = "select * from users where user_hash=:user_hash";
                $sth = Connection::getConnection()->prepare($sql);

		        $result = $sth->execute(array(':user_hash' => $_COOKIE['user_hash']));
		        $user = $sth->fetch();
                
                if ($user)
                    $this->set_user($user);   
            }        
        }
        return isset($_SESSION['logged_in']) and $_SESSION['logged_in'];
    }
  
    function is_admin(){
        return $this->is_logged_in() && @$_SESSION['is_admin']=='1';
    }
    
    function set_user($user){
        $_SESSION['logged_in'] = true;
        $_SESSION['is_admin']=$user['is_admin'];
        $_SESSION['user_id']=$user['id'];
    }
    
    function do_login(){
        $users = Table::getTable('users', getModuleInstance('users'));
        $user = $users->loadObject(array('exact'=>array('username' => $_POST['login'], 
                                         'password' => md5($_POST['password']))));
        
        if ($user){
            $this->set_user($user);
            $randomstring = md5(random_string(10));
            setcookie('user_hash', $randomstring, time()+ 60 * 60 * 24 * 10, '/');
            $users->updateObject(array('id'=>$user['id'], 'user_hash'=> $randomstring));
        }
        else
            addMessage('Неверный логин или пароль');

        redirect('/');

    }
    function do_logout(){
        unset($_SESSION['logged_in']);
        unset($_SESSION['is_admin']);
        setcookie("user_hash", "");
		redirect('/');
    }
}

?>
