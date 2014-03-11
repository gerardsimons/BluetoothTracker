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
}
?>