<?php

class Mana_Db_Exception_Validation extends Exception {
	protected $_errors;
	public function __construct($errors) {
		$this->_errors = $errors;
	}
	public function getErrors() {
		return $this->_errors;
	}
}