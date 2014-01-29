<?php
class APIStatus extends APISub
{
	public function status() {
		$sessiontimeout = $this->up->sessiontimeout;
		$sessiontimeoutnoaction = $this->up->sessiontimeoutnoaction;
		$sessionid = $this->up->sessionid;
		return array(
			"active" => true,
			"sessiontimeout" => $sessiontimeout,
			"sessiontimeoutnoaction" => $sessiontimeoutnoaction,
			"sessionid" => $sessionid
		);
	}
}
?>