<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Core_Model_Condition_Combine extends Mage_Rule_Model_Condition_Combine {
    protected $_initialData;
    public function __construct($data = array())
    {
        $this->_initialData = $data;
        parent::__construct();
    }

    protected function _construct() {
        $this->_data = $this->_initialData;
        $this->_initialData = null;
    }
}