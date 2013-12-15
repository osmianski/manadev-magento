<?php
/**
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Core_Exception_Validation extends Exception {
	protected $_errors;
	public function __construct($errors) {
		$this->_errors = $errors;
	}

    /**
     * @return array
     */
    public function getErrors() {
		return $this->_errors;
	}
}