<?php
@include_once("../apisub.php"); //to enable code hinting
class APITracking extends APISub
{
	public $loginreq = array("primary", "secondary", "islabel");
	public $needstatusrequest = array();
	public $enablecaching = array("islabel");
	public $resetcaching = array("primary", "secondary");
	
	public function primary() {
		
	}
	
	public function secondary() {
		
	}
	
	public function isLabel($mac) {
		$mac = $this->up->shared->formatMAC($mac);
		
		if ($mac === false) return false;
		
		$res = $this->getRow("SELECT * FROM Labels WHERE MAC=? AND Active=?", array($mac, 1));
		if ($res === false) return false;
		
		$labelid = $res["ID"];
		
		return $this->up->shared->getLabelData($labelid);
	}
}
?>