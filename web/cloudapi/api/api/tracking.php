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
	
	public function performLocationInterpolation($labelid) {
		$lat = false;
		$lon = false;
		$accuracy = false;
		$timestamp = false;
		$active = false;
		
		//perform interpolation
		
		$data = array(
			"lat" => $lat,
			"lon" => $lon,
			"accuracy" => $accuracy,
			"timestamp" => $timestamp,
			"active" => $active
		);
		
		return false;
	}
	
	public function isLabel($mac) {
		//format MAC address
		$mac = $this->up->label->formatMAC($mac);
		
		//if MAC address not valid, fail
		if ($mac === false) return false;
		
		//check if label exists
		$res = $this->getRow("SELECT * FROM Labels WHERE MAC=? AND Active=?", array($mac, 1));
		if ($res === false) return false;
		
		//if label exists, return label data
		$labelid = $res["ID"];
		return $this->up->label->getLabelData($labelid);
	}
}
?>