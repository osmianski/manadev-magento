<?php
/**
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method string getScopeName()
 */
class Mana_Db_Model_Entity extends Mage_Core_Model_Abstract {
    protected $_scope;

    public function __construct($data = null) {
        if (is_array($data)) {
            if (isset($data['scope'])) {
                $this->_scope = $data['scope'];
            }
        }

        parent::__construct($data);
    }

    protected function _construct() {
        $this->_initScope();
    }

    protected function _initScope() {

        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        $this->_init($db->getScopedName($this->_scope), 'id');

        return $this;
    }
}