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
 * @method Mana_Db_Model_Entity setEditMassaction(int $value)
 * @method string getDefaultFormulas()
 * @method int getStoreId()
 * @method Mana_Db_Model_Entity setStoreId(int $value)
 * @method int getGlobalId()
 * @method Mana_Seo_Model_Schema setGlobalId(int $value)
 * @method int getPrimaryId()
 * @method Mana_Seo_Model_Schema setPrimaryId(int $value)
 * @method int getPrimaryGlobalId()
 * @method Mana_Seo_Model_Schema setPrimaryGlobalId(int $value)
 */
class Mana_Db_Model_Entity extends Mage_Core_Model_Abstract {
    protected $_scope;
    protected $_isIndexingDisabled = false;
    protected $_jsons;

    public function __construct($data = null) {
        if (is_array($data)) {
            if (isset($data['scope'])) {
                $this->_scope = $data['scope'];
                unset($data['scope']);
            }
        }

        parent::__construct($data);
    }

    public function loadForStore($id, $storeId, $fieldName = 'global_id') {
        $this->_getResource()->loadForStore($this, $id, $storeId, $fieldName);
        $this->_afterLoad();
        $this->setOrigData();
        $this->_hasDataChanges = false;

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

    protected function _getResource() {
        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        if (empty($this->_resourceName)) {
            Mage::throwException(Mage::helper('core')->__('Resource is not set.'));
        }

        return $db->getResourceSingleton($this->_resourceName);
    }

    public function getScope() {
        return $this->_scope;
    }

    public function assignDefaultValues() {
        $this->setDummy(true);
        return $this;
    }

    public function addGridCellData($cells) {
        foreach ($cells as $column => $cell) {
            $this->overrideData($column, $cell['value']);
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

    public function validate($dataSource) {
        /* @var $dbConfig Mana_Db_Helper_Config */
        $dbConfig = Mage::helper('mana_db/config');

        foreach ($dbConfig->getScopeValidators($this->_scope) as $validator) {
            call_user_func(array($this, $validator->getName()), $dataSource);
        }

        foreach ($dbConfig->getScopeFields($this->_scope) as $fieldXml) {
            foreach ($dbConfig->getFieldValidators($fieldXml) as $validator) {
                call_user_func(array($this, $validator->getName()), $dataSource, $fieldXml->getName());
            }
        }
        return $this;
    }

    public function postValidate($dataSource) {
        /* @var $dbConfig Mana_Db_Helper_Config */
        $dbConfig = Mage::helper('mana_db/config');

        foreach ($dbConfig->getScopePostValidators($this->_scope) as $validator) {
            call_user_func(array($this, $validator->getName()), $dataSource);
        }

        foreach ($dbConfig->getScopeFields($this->_scope) as $fieldXml) {
            foreach ($dbConfig->getFieldPostValidators($fieldXml) as $validator) {
                call_user_func(array($this, $validator->getName()), $dataSource, $fieldXml->getName());
            }
        }

        return $this;
    }

    public function isNotEmpty($dataSource, $field) {
        if (!$this->getData($field)) {
            throw new Mana_Db_Exception_Validation(Mage::helper('mana_db')->__("Please fill in '%s'.", $dataSource->getLabel($this->getScope(), $field)));
        }
    }

    public function getJson($key) {
        if (!$this->_jsons) {
            $this->_jsons = array();
        }
        if (!isset($this->_jsons[$key])) {
            $data = $this->getData($key);
            if (empty($data)) {
                $this->_jsons[$key] = array();
            }
            else {
                $this->_jsons[$key] = json_decode($data, true);
                if (is_null($this->_jsons[$key])) {
                    throw new Exception(json_last_error());
                }
            }
        }

        return $this->_jsons[$key];
    }

    public function overrideData($key, $value = null) {
       $this->setData($key, $value);

        if (is_array($key)) {
            foreach (array_keys($key) as $aKey) {
                $this->_setDefaultMask($aKey, true);
            }
        }
        else {
            $this->_setDefaultMask($key, true);
        }
        return $this;
    }

    protected function _setDefaultMask($key, $value) {
        /* @var $dbConfig Mana_Db_Helper_Config */
        $dbConfig = Mage::helper('mana_db/config');

        if (($field = $dbConfig->getScopeField($this->_scope, $key)) && isset($field->no)) {
            /* @var $db Mana_Db_Helper_Data */
            $db = Mage::helper('mana_db');

            $no = (int)(string)$field->no;
            $maskIndex = $db->getMaskIndex($no);
            $mask = isset($this->_data['default_mask' . $maskIndex]) ? $this->_data['default_mask' . $maskIndex] : 0;
            if ($value) {
                $mask = $mask | $db->getMask($no);
            }
            else {
                $mask = $mask & ~$db->getMask($no);
            }
            $this->setData('default_mask' . $maskIndex, $mask);
        }
    }

    public function useDefaultData($key) {
        if (is_array($key)) {
            foreach ($key as $aKey) {
                $this->_setDefaultMask($aKey, false);
            }
        }
        else {
            $this->_setDefaultMask($key, false);
        }

        return $this;
    }

    public function isUsingDefaultData($key, $in = '_data') {
        if (($field = $this->dbConfigHelper()->getScopeField($this->_scope, $key)) && isset($field->no)) {
            /* @var $db Mana_Db_Helper_Data */
            $db = Mage::helper('mana_db');

            $no = (int)(string)$field->no;
            $maskIndex = $db->getMaskIndex($no);
            $data = $this->$in;
            $mask = isset($data['default_mask' . $maskIndex]) ? $data['default_mask' . $maskIndex] : 0;
            return !($mask & $db->getMask($no));
        }
        return false;
    }

    public function isDefaultable($key) {
        return ($field = $this->dbConfigHelper()->getScopeField($this->_scope, $key)) && isset($field->no);
    }

    public function getFieldsXml() {
        return $this->dbConfigHelper()->getScopeFields($this->_scope);
    }

    public function getDefaultableFields() {
        $result = array();
        foreach ($this->getFieldsXml() as $fieldXml) {
            if (isset($fieldXml->no)) {
                $result[] = (string)$fieldXml->name;
            }
        }
        return $result;
    }

    public function __call($method, $args) {
        if (substr($method, 0, 8) == 'override') {
            //Varien_Profiler::start('SETTER: '.get_class($this).'::'.$method);
            $key = $this->_underscore(substr($method, 8));
            $result = $this->overrideData($key, isset($args[0]) ? $args[0] : null);

            //Varien_Profiler::stop('SETTER: '.get_class($this).'::'.$method);
            return $result;
        }
        return parent::__call($method, $args);
    }

    /**
     * Init indexing process after category data commit
     *
     * @return Mage_Catalog_Model_Category
     */
    public function afterCommitCallback() {
        parent::afterCommitCallback();
        if (!Mage::registry('m_prevent_indexing_on_save')) {
            $this->getIndexerSingleton()->processEntityAction($this, $this->getScope(), Mage_Index_Model_Event::TYPE_SAVE);
        }
        return $this;
    }

    #region Dependencies
    /**
     * @return Mana_Db_Helper_Config
     */
    public function dbConfigHelper() {
        return Mage::helper('mana_db/config');
    }

    /**
     * @return Mage_Index_Model_Indexer
     */
    public function getIndexerSingleton() {
        return Mage::getSingleton('index/indexer');
    }
    #endregion
}