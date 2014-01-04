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
abstract class Mana_AttributePage_Model_AttributePage_Abstract extends Mage_Core_Model_Abstract {
    const DM_IS_ACTIVE = 0;
    const DM_TITLE = 1;
    const DM_DESCRIPTION = 2;
    const DM_IMAGE = 3;
    const DM_INCLUDE_IN_MENU = 4;
    const DM_URL_KEY = 5;
    const DM_TEMPLATE = 6;
    const DM_SHOW_ALPHABETIC_SEARCH = 7;
    const DM_PAGE_LAYOUT = 8;
    const DM_LAYOUT_XML = 9;
    const DM_CUSTOM_DESIGN_ACTIVE_FROM = 10;
    const DM_CUSTOM_DESIGN_ACTIVE_TO = 11;
    const DM_CUSTOM_DESIGN = 12;
    const DM_CUSTOM_LAYOUT_XML = 13;
    const DM_META_TITLE = 14;
    const DM_META_KEYWORDS = 15;
    const DM_META_DESCRIPTION = 16;
    const DM_OPTION_PAGE_INCLUDE_FILTER_NAME = 17;
    const DM_OPTION_PAGE_IMAGE = 18;
    const DM_OPTION_PAGE_INCLUDE_IN_MENU = 19;
    const DM_OPTION_PAGE_IS_ACTIVE = 20;
    const DM_OPTION_PAGE_SHOW_PRODUCTS = 21;
    const DM_OPTION_PAGE_AVAILABLE_SORT_BY = 22;
    const DM_OPTION_PAGE_DEFAULT_SORT_BY = 23;
    const DM_OPTION_PAGE_PRICE_STEP = 24;
    const DM_OPTION_PAGE_PAGE_LAYOUT = 25;
    const DM_OPTION_PAGE_LAYOUT_XML = 26;
    const DM_OPTION_PAGE_CUSTOM_DESIGN_ACTIVE_FROM = 27;
    const DM_OPTION_PAGE_CUSTOM_DESIGN_ACTIVE_TO = 28;
    const DM_OPTION_PAGE_CUSTOM_DESIGN = 29;
    const DM_OPTION_PAGE_CUSTOM_LAYOUT_XML = 30;

    const DM_COLUMN_COUNT = 32;
    const DM_SHOW_FEATURED_OPTIONS = 33;
    const DM_IMAGE_WIDTH = 34;
    const DM_IMAGE_HEIGHT = 35;
    const DM_OPTION_PAGE_IS_FEATURED = 36;
    const DM_OPTION_PAGE_IMAGE_WIDTH = 37;
    const DM_OPTION_PAGE_IMAGE_HEIGHT = 38;
    const DM_OPTION_PAGE_FEATURED_IMAGE_WIDTH = 39;
    const DM_OPTION_PAGE_FEATURED_IMAGE_HEIGHT = 40;
    const DM_OPTION_PAGE_PRODUCT_IMAGE_WIDTH = 41;
    const DM_OPTION_PAGE_PRODUCT_IMAGE_HEIGHT = 42;
    const DM_OPTION_PAGE_SIDEBAR_IMAGE_WIDTH = 43;
    const DM_OPTION_PAGE_SIDEBAR_IMAGE_HEIGHT = 44;
    const DM_POSITION = 45;

    const MAX_ATTRIBUTE_COUNT = 5;

    public function validate() {
        $t = Mage::helper('mana_attributepage');
        $errors = array();

        if ($this->adminHelper()->isGlobal() && !($this->getData('attribute_id_0'))) {
            $errors[] = $t->__('At least one attribute have to be selected');
        }
        if ($this->dbHelper()->isModelContainsCustomSetting($this, self::DM_TITLE) &&
            !trim($this->getData('title')))
        {
            $errors[] = $t->__('Please fill in %s field', $t->__('Title'));
        }
        if ($this->dbHelper()->isModelContainsCustomSetting($this, self::DM_DESCRIPTION) &&
            !trim($this->getData('description')))
        {
            $errors[] = $t->__('Please fill in %s field', $t->__('Description'));
        }
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
        if (($this->adminHelper()->isGlobal() ||
            $this->dbHelper()->isModelContainsCustomSetting($this, self::DM_OPTION_PAGE_AVAILABLE_SORT_BY)) &&
            !trim($this->getData('option_page_available_sort_by')))
        {
            $errors[] = $t->__('Please choose at least one option in %s field', $t->__('Available Sort By'));
        }
        if ($this->adminHelper()->isGlobal() ||
            $this->dbHelper()->isModelContainsCustomSetting($this, self::DM_OPTION_PAGE_DEFAULT_SORT_BY))
        {
            if (!in_array($this->getData('option_page_default_sort_by'),
                explode(',', $this->getData('option_page_available_sort_by'))))
            {
                $errors[] = $t->__('Default Sort By value is not selected in Available Sort By');
            }
        }
        else {
            if (($global = Mage::registry('m_global_flat_model')) &&
                !in_array($global->getData('option_page_default_sort_by'),
                    explode(',', $global->getData('option_page_available_sort_by'))))
            {
                $errors[] = $t->__('Default Sort By value is not selected in Available Sort By');
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

    public function setDefaults() {
        $this->getResource()->setDefaults($this);

        return $this;
    }

    /**
     * Retrieve model resource
     *
     * @return Mana_AttributePage_Resource_AttributePage_Abstract
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