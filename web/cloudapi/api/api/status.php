<?php
@include_once("../apisub.php"); //to enable code hinting
class APIStatus extends APISub
{
	//get API status and initialize some variables required later on during authentication
	public function status() {
		$sessiontimeout = $this->up->sessiontimeout;
		$sessiontimeoutnoaction = $this->up->sessiontimeoutnoaction;
		$sessionid = $this->up->sessionid;
		
		$this->session["status"] = array();
		
		$timestamp = time();
		$this->session["status"]["ts"] = $timestamp;
		
		return array(
			"active" => true,
			"sessiontimeout" => $sessiontimeout,
			"sessiontimeoutnoaction" => $sessiontimeoutnoaction,
			"sessionid" => $sessionid,
			"timestamp" => $timestamp
		);
	}
}
?>