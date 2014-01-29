<?php
//this class is the base for all API subclasses
class APISub
{
	public $up;
	public $db;
	public $session = array();
	
	public $loginreq = array();
	public $enablecaching = array();
	public $resetcaching = array();
	
	//setting up the framework
	public function __construct($mainclassref) {
		$this->up = &$mainclassref;
		$this->db = &$this->up->db;
		$this->session = &$this->up->session;
	}
	
	//database calling relay functions
	public function query($sql) {
		return $this->up->query($sql);
	}
	public function getRows($sql) {
		return $this->up->getRows($sql);
	}
	public function getRow($sql) {
		return $this->up->getRow($sql);
	}
	
	//throw error
	public function throwError($msg, $type = 5) {
		return $this->up->throwError($type, $msg);
	}
}
?>