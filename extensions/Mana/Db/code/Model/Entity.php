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
 * @method int getEditStatus()
 * @method Mana_Db_Model_Entity setEditStatus(int $value)
 * @method Mana_Db_Model_Entity setEditSessionId(int $value)
 */
class Mana_Db_Model_Entity extends Mage_Core_Model_Abstract {
    protected $_scope;
    protected $_isIndexingDisabled = false;

    public function __construct($data = null) {
        if (is_array($data)) {
            if (isset($data['scope'])) {
                $this->_scope = $data['scope'];
                unset($data['scope']);
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

    protected function _getResource() {
        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        if (empty($this->_resourceName)) {
            Mage::throwException(Mage::helper('core')->__('Resource is not set.'));
        }

        return $db->getResourceSingleton($this->_resourceName);
    }

    public function assignDefaultValues() {
        $this->setDummy(true);
        return $this;
    }

    public function addGridCellData($cells) {
        foreach ($cells as $column => $cell) {
            $this->setData($column, $cell['value']);
        }
        return $this;
    }

    public function loadEdited($id, $sessionId) {
        $this->_beforeLoad($id);

        /* @var $resource Mana_Db_Resource_Entity */
        $resource = $this->_getResource();
        $resource->loadEdited($this, $id, $sessionId);

        $this->_afterLoad();
        $this->setOrigData();
        $this->_hasDataChanges = false;
        return $this;
    }

    public function disableIndexing() {
        $this->_isIndexingDisabled = true;
        return $this;
    }

    public function enableIndexing() {
        $this->_isIndexingDisabled = false;
        return $this;
    }

    public function updateIndexes() {
        return $this;
    }
}