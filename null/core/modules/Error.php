<?php

class Error extends Base{
    private $message;
      
    function showContents(){
        print "<div>На сервере произошла следующая ошибка</div>";
        print $this->message;
    }

    function showError($message){
        $this->message = $message;
        $this->showPage();
    }
    
}
?>