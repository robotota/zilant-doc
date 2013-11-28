<?php

require_once('PHPUnit/Framework.php');

class AllTest {
	public static function suite(){
		$suite = new PHPUnit_Framework_TestSuite();
		$iterator = new DirectoryIterator(".");
		foreach($iterator as $fileinfo){
		    if ($fileinfo->isDir()){
		        $filename = $fileinfo->getFilename();
		        if ($filename[0]!='.' && $filename != 'null' && $filename != 'doc')
             		$suite->addTestSuite(new ProjectSuite($filename));

		    }    
		}

		return $suite;
	}
}

function createNullTestCase($name){
eval("class {$name}NullTestCase extends NullTestCase {
    public function getAddress(){
        return '$name';
    }    
}");
}

class ProjectSuite extends PHPUnit_Framework_TestSuite{
    
    public function setUp(){
        print "\n".exec("cd {$this->name} && rm -f test/cookie.txt && ./upload");
    }
    
    public function __construct($name = ""){
       parent::__construct($name);
       require_once($name."/test/Test.php");
       createNullTestCase($name);
       $this->addTestSuite($name."NullTestCase");
       $this->addTestSuite(ucfirst($name)."Test");
    }

}
?>
