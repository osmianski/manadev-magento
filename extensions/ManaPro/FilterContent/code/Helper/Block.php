<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterContent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
abstract class ManaPro_FilterContent_Helper_Block extends Mage_Core_Helper_Abstract {
    /**
     * @param Mage_Core_Block_Abstract $block
     * @param string $key
     */
    public function before($block, $key) {
    }

    /**
     * @param Mage_Core_Block_Abstract $block
     * @param string $key
     * @param Varien_Object $htmlObject
     */
    public function after($block, $key, $htmlObject) {
    }

    #region Dependencies

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    /**
     * @return ManaPro_FilterContent_Helper_Factory
     */
    public function factoryHelper() {
        return Mage::helper('manapro_filtercontent/factory');
    }

    /**
     * @return ManaPro_FilterContent_Helper_Data
     */
    public function helper() {
        return Mage::helper('manapro_filtercontent');
    }

    /**
     * @return ManaPro_FilterContent_Helper_Renderer
     */
    public function rendererHelper() {
        return Mage::helper('manapro_filtercontent/renderer');
    }
    #endregion
}