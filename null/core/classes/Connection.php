<?php
class Connection{
	static private $connection = null;
	public static function getConnection(){
        global $mysql_config;
        if (is_null(self::$connection)){
			self::$connection = new PDO("mysql:host=localhost;dbname=".$mysql_config['dbname'], $mysql_config['login'], $mysql_config['password']);
			self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			self::$connection->exec("set names utf8");
	    }
	    return self::$connection;
	}
}

?>