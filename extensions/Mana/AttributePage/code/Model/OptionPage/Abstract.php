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
abstract class Mana_AttributePage_Model_OptionPage_Abstract extends Mage_Core_Model_Abstract {
    const DM_IS_ACTIVE = 0;
    const DM_TITLE = 1;
    const DM_DESCRIPTION = 2;
    const DM_IMAGE = 3;
    const DM_INCLUDE_IN_MENU = 4;
    const DM_URL_KEY = 5;
    const DM_SHOW_PRODUCTS = 6;
    const DM_AVAILABLE_SORT_BY = 7;
    const DM_DEFAULT_SORT_BY = 8;
    const DM_PRICE_STEP = 9;
    const DM_PAGE_LAYOUT = 10;
    const DM_LAYOUT_XML = 11;
    const DM_CUSTOM_DESIGN_ACTIVE_FROM = 12;
    const DM_CUSTOM_DESIGN_ACTIVE_TO = 13;
    const DM_CUSTOM_DESIGN = 14;
    const DM_CUSTOM_LAYOUT_XML = 15;
    const DM_META_TITLE = 16;
    const DM_META_KEYWORDS = 17;
    const DM_META_DESCRIPTION = 18;
    const DM_IS_FEATURED = 19;
    const DM_IMAGE_WIDTH = 20;
    const DM_IMAGE_HEIGHT = 21;
    const DM_FEATURED_IMAGE = 22;
    const DM_FEATURED_IMAGE_WIDTH = 23;
    const DM_FEATURED_IMAGE_HEIGHT = 24;
    const DM_PRODUCT_IMAGE = 25;
    const DM_PRODUCT_IMAGE_WIDTH = 26;
    const DM_PRODUCT_IMAGE_HEIGHT = 27;
    const DM_SIDEBAR_IMAGE = 28;
    const DM_SIDEBAR_IMAGE_WIDTH = 29;
    const DM_SIDEBAR_IMAGE_HEIGHT = 30;

    const DM_POSITION = 32;

    public function validate() {
        $t = Mage::helper('mana_attributepage');
        $errors = array();

        if ($this->dbHelper()->isModelContainsCustomSetting($this, self::DM_TITLE) &&
            !trim($this->getData('title')))
        {
            $errors[] = $t->__('Please fill in %s field', $t->__('Title'));
        }
/*        if ($this->dbHelper()->isModelContainsCustomSetting($this, self::DM_DESCRIPTION) &&
            !trim($this->getData('description')))
        {
            $errors[] = $t->__('Please fill in %s field', $t->__('Description'));
        }*/
        if ($this->dbHelper()->isModelContainsCustomSetting($this, self::DM_URL_KEY) &&
            !trim($this->getData('url_key')))
        {
            $errors[] = $t->__('Please fill in %s field', $t->__('URL Key'));
        }
        if ($this->dbHelper()->isModelContainsCustomSetting($this, self::DM_META_TITLE) &&
            !trim($this->getData('meta_title')))
        {
            $errors[] = $t->__('Please fill in %s field', $t->__('Page Title'));
        }
        if ($this->dbHelper()->isModelContainsCustomSetting($this, self::DM_AVAILABLE_SORT_BY) &&
            !trim($this->getData('available_sort_by')))
        {
            $errors[] = $t->__('Please choose at least one option in %s field', $t->__('Available Sort By'));
        }
        if ($this->dbHelper()->isModelContainsCustomSetting($this, self::DM_DEFAULT_SORT_BY)) {
            if ($this->adminHelper()->isGlobal()) {
                if ($this->dbHelper()->isModelContainsCustomSetting($this, self::DM_AVAILABLE_SORT_BY)) {
                    if (!in_array($this->getData('default_sort_by'), explode(',', $this->getData('available_sort_by')))) {
                        $errors[] = $t->__('Default Sort By value is not selected in Available Sort By');
                    }
                }
                else {
                    $attributePage = Mage::registry('m_attribute_page');
                    if (!in_array($this->getData('default_sort_by'), explode(',', $attributePage->getData('option_page_available_sort_by')))) {
                        $errors[] = $t->__('Default Sort By value is not selected in Available Sort By');
                    }
                }
            }
            else {
                $global = Mage::registry('m_global_flat_model');
                if ($this->dbHelper()->isModelContainsCustomSetting($this, self::DM_AVAILABLE_SORT_BY)) {
                    if (!in_array($this->getData('default_sort_by'), explode(',', $this->getData('available_sort_by')))) {
                        $errors[] = $t->__('Default Sort By value is not selected in Available Sort By');
                    }
                }
                elseif ($this->dbHelper()->isModelContainsCustomSetting($global, self::DM_AVAILABLE_SORT_BY)) {
                    if (!in_array($this->getData('default_sort_by'), explode(',', $global->getData('available_sort_by')))) {
                        $errors[] = $t->__('Default Sort By value is not selected in Available Sort By');
                    }
                }
                else {
                    $attributePage = Mage::registry('m_attribute_page');
                    if (!in_array($this->getData('default_sort_by'), explode(',', $attributePage->getData('option_page_available_sort_by')))) {
                        $errors[] = $t->__('Default Sort By value is not selected in Available Sort By');
                    }
                }
            }
        }
        if ($this->dbHelper()->isModelContainsCustomSetting($this, self::DM_POSITION) &&
            !trim($this->getData('position')))
        {
            $errors[] = $t->__('Please fill in %s field', $t->__('Position'));
        }
        if (count($errors)) {
			throw new Mana_Core_Exception_Validation($errors);
        }
    }
    /**
     * Retrieve model resource
     *
     * @return Mana_AttributePage_Resource_OptionPage_Abstract
     */
    public function getResource() {
        return parent::getResource();
    }

    #region Dependencies
    /**
     * @return Mage_Index_Model_Indexer
     */
    public function getIndexerSingleton() {
        return Mage::getSingleton('index/indexer');
    }

    /**
     * @return Mana_Core_Helper_Db
     */
    public function dbHelper() {
        return Mage::helper('mana_core/db');
    }

    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }

    #endregion
}