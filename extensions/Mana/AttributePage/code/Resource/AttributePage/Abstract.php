<?php
/** 
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
abstract class Mana_AttributePage_Resource_AttributePage_Abstract extends Mage_Core_Model_Mysql4_Abstract {
    /**
     * @param Varien_Object $object
     * @return $this
     */
    public function setDefaults($object) {
        if (!$object->getData('_skip_non_defaultables')) {
            $object
                ->setData('is_active', 1)
                ->setData('include_in_menu', 1)
                ->setData('show_alphabetic_search', 1);
        }
        if ($object->getData('_add_option_page_defaults')) {
            $object
                ->setData('option_page_is_active', 1)
                ->setData('option_page_include_in_menu', 1)
                ->setData('option_page_show_products', 1)
                ->setData('option_page_available_sort_by', array_keys($this->getSortBySourceModel()->getAllOptions()))
                ->setData('option_page_default_sort_by', Mage::getStoreConfig('catalog/frontend/default_sort_by'));
        }
        return $this;
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

    /**
     * @return Mana_Core_Helper_Db_Aggregate
     */
    public function dbAggregateHelper() {
        return Mage::helper('mana_core/db_aggregate');
    }

    /**
     * @return Mana_AttributePage_Helper_Data
     */
    public function attributePageHelper() {
        return Mage::helper('mana_attributepage');
    }
    /**
     * @return Mana_AttributePage_Model_Source_SortBy
     */
    public function getSortBySourceModel() {
        return Mage::getSingleton('mana_attributepage/source_sortBy');
    }
    #endregion
}