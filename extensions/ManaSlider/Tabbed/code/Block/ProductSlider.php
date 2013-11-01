<?php
/** 
 * @category    Mana
 * @package     ManaSlider_Tabbed
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method ManaSlider_Tabbed_Model_Tab[] getTabs()
 * @method int getHeight()
 */
class ManaSlider_Tabbed_Block_ProductSlider extends Mage_Catalog_Block_Product_Abstract
{
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('manaslider/tabbed/product-slider.phtml');
    }

    #region Dependencies
    /**
     * @return Mana_Core_Helper_Layout
     */
    public function layoutHelper() {
        return Mage::helper('mana_core/layout');
    }

    /**
     * @return Mage_Core_Model_Layout
     */
    public function getLayout() {
        return Mage::getSingleton('core/layout');
    }

    /**
     * @return Mana_Core_Helper_Js
     */
    public function jsHelper() {
        return Mage::helper('mana_core/js');
    }

    /**
     * @return Mana_Core_Helper_Json
     */
    public function jsonHelper() {
        return Mage::helper('mana_core/json');
    }

    #endregion
}