<?php
require_once "config/config.php";
require_once "core/framework.php";
require_once "core/utils.php";

ob_start();
session_start();

try{

    $auth = new Auth();

    if (!$auth->is_logged_in()){
        $module = "Auth";
        $moduleInstance = $auth;
    }    
    else{
        $module = nvl($_GET['module'], 'DefaultPage');
        $moduleInstance = getModuleInstance($module);
    }

    $action = &$_GET['action'];
    if (!empty($action)){
        $action_full_name = 'do_'.$action;
        $moduleInstance->$action_full_name();
        redirect(call($module));
    }
    else
        $moduleInstance->showPage();
}
catch(Exception $e){
    ob_clean();
    $error = getModuleInstance('Error');
    
    $error->showError($e->getMessage());
}
?>