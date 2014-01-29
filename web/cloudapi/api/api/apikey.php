<?php
class APIAPIKey extends APISub
{
	public $loginreq = array("");
	public $enablecaching = array("isactive");
	public $resetcaching = array("");
	
	//don't put this function in the $loginreq array!
	public function isActive($key) {
		$this->up->apikeymsg = "";
		return true;								//UNDER CONSTRUCTION
	}
}
?>