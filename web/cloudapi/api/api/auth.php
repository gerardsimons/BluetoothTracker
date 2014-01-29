<?php
class APIAuth extends APISub
{
	public $loginreq = array("");
	public $enablecaching = array("");
	public $resetcaching = array("");
	
	public function isLoggedIn() {
		return false;
	}
}
?>