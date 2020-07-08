<?php
class FatModel {
	protected $_model;
	
	protected $error;
	
	function getError(){
		return $this->error;
	}
	
	function __construct() {
		$this->_model = get_class($this);
		$this->error = '';
	}
	
	function __destruct() {
	}
}