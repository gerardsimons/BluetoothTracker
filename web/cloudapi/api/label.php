<?php
@include_once("../apisub.php"); //to enable code hinting
class APILabel extends APISub
{
	public $loginreq = array("getlabeldata", "getlabels", "getlocation");
	public $needstatusrequest = array();
	public $enablecaching = array("getlabeldata", "getlabels", "getlocation");
	public $resetcaching = array();
	
	//get data about specific label
	public function getLabelData($labelid) {
		//get user ID and check if logged in
		$userid = $this->up->auth->getUserID();
		if ($userid === 0) return false;
		
		//load label
		$res = $this->getRow("SELECT * FROM Labels WHERE ID=? AND Active=?", array($labelid, 1));
		if ($res === false) return false;
		
		//check if label is shared with current user
		if ($sharedres = $this->getRow("SELECT * FROM LabelSharing WHERE UserID=? AND LabelID=?", array($userid, $labelid))) $res["SharedData"] = $sharedres;
		
		//output the formatted data
		return $this->formatLabelData($res);
	}
	
	//get all labels of user
	public function getLabels() {
		//get userid of current logged in user
		$userid = $this->up->auth->getUserID();
		
		//load any label IDs of labels shared with user
		$sharedids = array();
		$sharedlabels = array();
		$rows = $this->getRows("SELECT * FROM LabelSharing WHERE UserID=?", array($userid));
		if ($rows !== false)
		{
			foreach ($rows as $row)
			{
				$sharedlabels[$row["LabelID"]] = $row;
				$sharedids[] = $row["LabelID"];
			}
		}
		
		//load labels of user (owned and shared with)
		if (count($sharedids) > 0)
		{
			//has labels shared with user
			$fields = $sharedids;
			$fields[] = $userid;
			$fields[] = 1;
			$rows = $this->getRows("SELECT * FROM Labels WHERE (ID IN (".implode(",", array_fill(0, count($sharedids), "?")).") OR OwnerID=?) AND Active=?", $fields);
		}
		else //no labels shared with user
			$rows = $this->getRows("SELECT * FROM Labels WHERE OwnerID=? AND Active=?", array($userid, 1));
		if ($rows === false) return false;
		
		//process labels
		$labels = array();
		foreach ($rows as $row)
		{
			$id = $row["ID"];
			if (isset($sharedlabels[$id])) $row["SharedData"] = $sharedlabels[$id];
			$labeldata = $this->formatLabelData($row);
			if ($labeldata !== false) $labels[] = $labeldata;
		}
		
		return (count($labels) > 0) ? $labels: false;
	}
	
	//format output data to match protocol, used by getLabels and getLabelData
	private function formatLabelData($row) {
		$userid = $this->up->auth->getUserID();
		if ($userid === 0) return false;
		
		//format MAC address
		$mac = $this->formatMAC($row["MAC"]);
		if ($mac === false) return false;
		
		//load some data
		$labelid = $row["ID"];
		$labeltype = $row["Type"];
		$ownerid = $row["OwnerID"];
		$name = $row["Name"];
		$lost = $row["Lost"];
		$public = $row["Public"];
		
		$iconid = $row["IconID"];
		$pictureid = $row["PictureID"];
		
		//shared with user?
		$sharedwith = (isset($row["SharedData"])) ? true: false;
		if ($sharedwith == true)
		{
			$shareddata = $row["SharedData"];
			
			//check if sharing hasn't expired yet
			$timestamp = $row["Timestamp"];
			$timeout = $row["Timeout"];
			if ($timeout !== NULL)
			{
				if ($timestamp + $timeout < time()) $sharedwith = false;
			}
			if ($sharedwith == true)
			{
				//adjust label data for shared user
				if ($shareddata["Name"] !== NULL) $name = $shareddata["Name"];
			}
		}
		
		//prepare datasets
		$minimal = array(
			"id" => $labelid,
			"mac" => $mac,
			"lost" => $lost
		);
		
		//check if current label type is allowed
		$labeltypes = $this->getLabelTypes();
		if (!isset($labeltypes[$labeltype])) return $minimal;
		
		//if label is not public, not owned by user and not shared with user, also return minimal information
		if ($public != 1 && $ownerid != $userid && $sharedwith == false) return $minimal;
		
		//output data specific for various rights
		$data = $minimal;
		$data["typeid"] = $labeltype;
		$data["type"] = $labeltypes[$labeltype];
		//include icon data
		$data["position"] = $this->processLocation($row);
		//settings & metadata
		
		if ($ownerid == $userid) //label owned by user
		{
			$data["ownerid"] = $ownerid;
			$data["name"] = $name;
			$data["shared"] = ($this->getRow("SELECT * FROM LabelSharing WHERE LabelID=? AND (Timeout IS NULL OR (Timeout IS NOT NULL AND Timestamp+Timeout<?))", array($labelid, time()))) ? true: false;
		}
		elseif ($sharedwith == true) //label shared with user
		{
			$data["ownerid"] = $ownerid;
			$data["name"] = $name;
			$data["shared"] = true;
		}
		elseif ($public == 1) //public label
		{
			
		}
		return $data;
	}
	
	//function to get the latest known location of a label
	public function getLocation($labelid) {
		//load label data (to centralize rights checking)
		$labeldata = $this->getLabelData($labelid);
		//return position if this was returned when loading the label data
		return (isset($labeldata["position"])) ? $labeldata["position"]: false;
	}
	
	//function to retrieve the location for a given label, used by getLocation and formatLabelData
	private function processLocation($row) {
		$lat = $row["Lat"];
		$lon = $row["Lon"];
		$accuracy = $row["Accuracy"];
		$timestamp = $row["TimestampLocation"];
		$active = $row["LocationActive"];
		
		//check if new location interpolation is required
		if ($lat === NULL || $lon === NULL || $accuracy === NULL || $timestamp === NULL || $active === NULL || (($timestamp + $this->up->locationinterval) < time()))
		{
			$data = $this->up->tracking->performLocationInterpolation($row["ID"]);
			if ($data !== false)
			{
				$lat = $data["lat"];
				$lon = $data["lon"];
				$accuracy = $data["accuracy"];
				$timestamp = $data["timestamp"];
				$active = $data["active"];
			}
		}
		
		if ($lat === NULL || $lon === NULL || $accuracy === NULL || $timestamp === NULL || $active === NULL) return false;
		
		return array(
			"lat" => $lat,
			"lon" => $lon,
			"accuracy" => $accuracy,
			"timestamp" => $timestamp,
			"active" => $active
		);
	}
	
	//get all allowed label types for the currently logged in user
	private function getLabelTypes() {
		$false = array();
		
		$usertype = $this->up->auth->getUserType();
		if ($usertype === 0) return $false;
		
		//caching
		if (isset($this->session["labeltypes"])) return $this->session["labeltypes"];
		
		$res = $this->getRow("SELECT * FROM UserTypes WHERE ID=?", array($usertype));
		if ($res === false) return $false;
		
		$labeltypes = explode(",", $res["LabelTypes"]);
		
		$fields = $labeltypes;
		$fields[] = 1;
		$rows = $this->getRows("SELECT * FROM LabelTypes WHERE ID IN (".implode(",", array_fill(0, count($labeltypes), "?")).") AND Active=?", $fields);
		
		$labeltypes = array();
		if ($rows !== false)
		{
			foreach ($rows as $row)
			{
				$id = $row["ID"];
				$name = $row["Name"];
				$labeltypes[$id] = $name;
			}
		}
		
		$this->session["labeltypes"] = $labeltypes;
		return $labeltypes;
	}
	
	//mac address formatting function
	public function formatMAC($mac) {
		$mac = strval($mac);
		$mac = strtolower($mac);
		if (substr($mac, 0, 2) == "0x") $mac = substr($mac, 2);
		$newstr = "";
		for ($i=0;$i<strlen($mac);$i++)
		{
			$char = substr($mac, $i, 1);
			$ascii = ord($char);
			if (($ascii >= 48 && $ascii <= 57) || ($ascii >= 97 && $ascii <= 102)) $newstr .= $char;
		}
		if (strlen($newstr) == 12)
		{
			$newmac = "";
			for ($i=0;$i<strlen($newstr);$i++)
			{
				if ($i > 0 && $i % 2 == 0) $newmac .= ":";
				$newmac .= substr($newstr, $i, 1);
			}
			return $newmac;
		}
		return false;
	}
}
?>