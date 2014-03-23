<?php
//this class contains all shared functions between API classes
//basically it is a sub class which is publicly available
class APIShared extends APISub
{
	//password hashing functions
	public function hashPass($pass, $salt) {
		return md5($pass.$salt);
	}
	
	public function hashPassSocialNetworkLogin($regtype, $networkuserid, $statusts) {
		return substr(md5($regtype.$networkuserid.$statusts), $statusts % 21);
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
	
	//get label data
	public function getLabelData($labelid) {
		return "This is a label ($labelid)!";
	}
}
?>