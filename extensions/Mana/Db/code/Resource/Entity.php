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
 *
 */
class Mana_Db_Resource_Entity extends Mage_Core_Model_Mysql4_Abstract {
    protected $_scope;

    public function __construct($data = null) {
        if (is_array($data)) {
            if (isset($data['scope'])) {
                $this->_scope = $data['scope'];
            }
        }

        parent::__construct();
    }

    /**
     * @param Mana_Db_Model_Entity $object
     * @param int $id
     * @param int $storeId
     * @param string $fieldName
     * @return Mana_Db_Resource_Entity
     */
    public function loadForStore($object, $id, $storeId, $fieldName) {
        $read = $this->_getReadAdapter();
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where("{$this->getMainTable()}.`$fieldName` = ?", $id)
            ->where("{$this->getMainTable()}.`store_id` = ?", $storeId);
        $data = $read->fetchRow($select);

        if ($data) {
            $object->setData($data);
        }

        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
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

    /**
     * @param Mana_Db_Model_Entity $object
     * @param int $id
     * @param int $sessionId
     * @return Mana_Db_Resource_Entity
     */
    public function loadEdited($object, $id, $sessionId) {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from($this->getMainTable())
            ->where("{$this->getMainTable()}.`edit_status` = ?", $id)
            ->where("{$this->getMainTable()}.`edit_session_id` = ?", $sessionId);
        $data = $read->fetchRow($select);

        if ($data) {
            $object->setData($data);
        }

        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
    }
}