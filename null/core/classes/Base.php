<?php

class UnimplementedException extends Exception{
};

abstract class Base {
    private $module;
    
    protected $isNewTicketAvailable;
    function __construct(){
        $this->module = nvl($_GET['module'], 'DefaultPage');
        $this->isNewTicketAvailable = true;    
    }
    
    function __call($method, $arguments){
        throw new UnimplementedException("Method $method is unimplemented in this class");
    }
    
    
    function showHeader(){
    //  PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"  
    ?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<link href="/css/style.css" rel="stylesheet" type="text/css" >

<link rel="stylesheet" href="/css/smoothness/jquery-ui-1.10.3.custom.css" />

<script src="/js/jquery-1.9.1.js" ></script>
<script src="/js/jquery-ui-1.10.3.custom.js"></script>
<script src="/js/scripts.js"></script>
  
  <script src="/js/chosen.jquery.js"></script>
  <link rel="stylesheet" href="/css/chosen.css" type="text/css">
  

</head>
<body>
<div id='all'>
    <?php
    }

    function showMessages(){
        if (isset($_SESSION['messages'])){
            foreach($_SESSION['messages'] as $message){
                print '<div class="message">'.$message.'</div>';
            }
            unset($_SESSION['messages']);
        }
          
    }
    
    function showPage(){
        global $auth, $CAPTION;;
        $this->showHeader();
        $this->showMessages();        
        if ($auth->is_logged_in()){
            $this->showSidebar();            
        }
        if ($auth->is_logged_in()){        
          print "<div id='main'>";
          print "<h1> $CAPTION </h1>";
        }
        
        $this->showContents();    

        if ($this->isNewTicketAvailable)
            print "<div>".hlink(call('tickets', '', array('mode'=>'new')), 'Написать замечание')."</div>";
        if ($auth->is_logged_in()){
          print "</div>";        
        }
        $this->showFooter();
        
    }
    
    
    function showFooter(){
    ?>
</div>
</body>
</html>
    <?php
    }
    
    public function menuItem($module, $action, $caption, $style=''){
        
        if (empty($style))
            $style=($module==$this->module?'selected':'pure');
            
        return "<li class='$style'>".hlink(call($module, $action), $caption)."</li>";
    }
    

    function showMenu(){
        global $auth;
        $value = "";        
        $value.="<div id='menu'>";     
        $value.="<ul>";
        if ($auth->is_admin())//страницы видимые только одмину
            $value=$value.$this->menuItem('__display_names', '', 'Имена таблиц')
                                 .$this->menuItem('__field_config', '', 'Свойства полей');
        //страницы видимые всем            
        $value=$value.$this->menuItem('DefaultPage','', 'Новости')
                           .$this->menuItem('users', '', 'Пользователи')
                           .$this->menuItem('tickets','', 'Замечания');
        
                           
        if (file_exists("./localMenu.php")){
            require_once("./localMenu.php");
            $value.=localMenu($this);
        }
           
        //$value .=   $this->menuItem('Auth','logout', 'Выход', 'exit')
        $value .= '</ul>';
        $value .= "</div>";
        return $value;
    }    

    function showSidebar(){
        global $auth;
        $page_head = "";
        
        $page_head.="<div id='sidebar'>";
        $page_head.=$this->showMenu();
        $calendar = getModuleInstance('calendar');
        $page_head .= "<div id='calendar_holder'> <div id='calendar_holder2'>".$calendar->show()."</div></div>";                     

        $page_head .= '</div>';

        $page_head .= "<div id='exit'> ".hlink(call('Auth', 'logout'), "Выход")."</div>";                               
        print $page_head;
        print "<script>
               $('#menu ul li').on('click', function (event) {window.location=($(this).children('a').attr('href'));});
               //$('#exit').on('click', function (event) {window.location=($(this).children('a').attr('href'));});
               </script>";

    }
    
    abstract function showContents();
    
}
?>
