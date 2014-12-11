<?php
/** 
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
abstract class Mana_Content_Resource_Page_Abstract extends Mage_Core_Model_Mysql4_Abstract {
    /**
     * @param Varien_Object $object
     * @return $this
     */
    public function setDefaults($object) {
        $object->setData('title', Mage::getStoreConfig('mana_content/book/default_title'));
        $object->setData('content', Mage::getStoreConfig('mana_content/book/default_content'));
        $object->setData('is_active', 1);
        return $this;
    }

    /**
     * Retrieve connection for write data
     *
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    protected function _getWriteAdapter()
    {
        return parent::_getWriteAdapter();
    }

    #region Dependencies
    /**
     * @return Mana_Core_Helper_Db
     */
    public function dbHelper() {
        return Mage::helper('mana_core/db');
    }

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    /**
     * @return Mana_Seo_Helper_Data
     */
    public function seoHelper() {
        return Mage::helper('mana_seo');
    }

    #endregion
}