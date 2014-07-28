<?php
//this class is the base for all API subclasses
class APISub
{
	public $up;
	public $db;
	public $session = array();
	public $insertid;
	
	public $loginreq = array();
	public $needstatusrequest = array();
	public $enablecaching = array();
	public $resetcaching = array();
	
	public $txt = array();
	
	//setting up the framework
	public function __construct($mainclassref) {
		$this->up = &$mainclassref;
		$this->db = &$this->up->db;
		$this->session = &$this->up->session;
		$this->insertid = &$this->up->insertid;
	}
	
	//database calling relay functions
	public function query($sql, $fields = false) {
		return $this->up->query($sql, $fields);
	}
	public function getRows($sql, $fields = false) {
		return $this->up->getRows($sql, $fields);
	}
	public function getRow($sql, $fields = false) {
		return $this->up->getRow($sql, $fields);
	}
	
	//throw error
	public function throwError($msg, $type = 5) {
		return $this->up->throwError($type, $msg);
	}
}
?>