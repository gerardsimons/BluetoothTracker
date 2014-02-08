<?php
@include_once("../apisub.php"); //to enable code hinting
class APITest extends APISub
{
	public $loginreq = array("");
	public $enablecaching = array("");
	public $resetcaching = array("");
	
	public function test() {
		if (isset($this->session["test"]))
			return true;
		else
		{
			$this->session["test"] = true;
			return false;
		}
		return true;
	}
}
?>