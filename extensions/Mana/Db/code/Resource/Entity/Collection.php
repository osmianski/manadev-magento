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
class Mana_Db_Resource_Entity_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    protected $_scope;

    public function __construct($resource = null) {
        if (is_array($resource)) {
            if (isset($resource['scope'])) {
                $this->_scope = $resource['scope'];
            }
            $resource = isset($resource['resource']) ? $resource['resource'] : null;
        }

        parent::__construct($resource);
    }

    protected function _construct() {
        $this->_initScope();
    }
    protected function _initScope() {

        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        $this->_init($db->getScopedName($this->_scope));
        return $this;
    }

    /**
     * Get resource instance
     *
     * @return Mage_Core_Model_Mysql4_Abstract
     */
    public function getResource() {
        if (empty($this->_resource)) {
            $this->_resource = Mage::helper('mana_db')->getResourceModel($this->getResourceModelName());
        }

        return $this->_resource;
    }
}