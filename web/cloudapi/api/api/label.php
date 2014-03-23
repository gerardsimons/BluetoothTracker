<?php
@include_once("../apisub.php"); //to enable code hinting
class APILabel extends APISub
{
	public $loginreq = array("getlabeldata", "getlabels", "getlocation");
	public $needstatusrequest = array();
	public $enablecaching = array("getlabeldata", "getlabels", "getlocation");
	public $resetcaching = array();
	
	public function getLabelData($labelid) {
		return $this->up->shared->getLabelData($labelid);
	}
	
	public function getLabels() {
		
	}
	
	public function getLocation($labelid) {
		
	}
}
?>