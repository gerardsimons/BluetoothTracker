<?php
class SubClass
{
	private $up;
	private $db;
	public function __construct($classref) {
		$this->up = $classref;
		$this->db = $this->up->db;
	}
	public function __call($name, $args) {
		$this->up->handleCall(get_class(), $name, $args);
	}
	public function doCall($name, $args) {
		return call_user_func_array(array($this, $name), $args);
	}
	
	private function privateFunction($argument1, $argument2) {
		echo "Private subclass function!<br>";
		$this->up->privateFunction();
		return ($argument1 + $argument2).$this->db;
	}
}

class MainClass
{
	public $db = "database!";
	
	public function __construct() {
		$name = "subclass";
		$this->$name = new $name($this);
	}
	
	public function __call($name, $args) {
		$this->handleCall("this", $name, $args);
	}
	
	public function handleCall($class, $name, $args) {
		$class = strtolower($class);
		echo "Starting function wrap for $class->$name<br>";
		if ($class == "this")
			$res = call_user_func_array(array($this, $name), $args);
		else
			$res = $this->$class->doCall($name, $args);
		echo "Function output: $res<br>";
		echo "Ending function wrap for $class->$name!<br>";
	}
	
	private function privateFunction() {
		echo "Private mainclass function!<br>";
	}
}

$test = new MainClass();

$test->privateFunction();
$test->subclass->privatefunction(1, 2);
?>