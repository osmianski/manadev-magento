<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterContent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for ManaPro_FilterContent module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class ManaPro_FilterContent_Helper_Data extends Mage_Core_Helper_Abstract {
    public function isActive() {
        return $this->isModuleOutputEnabled('manapro_filtercontent') &&
            Mage::getStoreConfigFlag('mana_filtercontent/general/is_active');
    }

    public function isContentKey($key) {
        return $this->coreHelper()->startsWith($key, 'm_filter_content_');
    }

    public function getContentKey($key) {
        return substr($key, strlen('m_filter_content_'));
    }

    public function getOriginalContentKey($key) {
        return 'm_original_content_' . $this->getContentKey($key);
    }

    /**
     * @param $key
     * @return Mage_Core_Model_Config_Element
     */
    public function getContentXml($key) {
        return Mage::getConfig()->getNode('manapro_filtercontent/content/'.$key);
    }

    public function getAllContentXmls() {
        return $this->coreHelper()->getSortedXmlChildren(Mage::getConfig()->getNode('manapro_filtercontent'), 'content');
    }

    public function getAllActionXmls() {
        return $this->coreHelper()->getSortedXmlChildren(Mage::getConfig()->getNode('manapro_filtercontent'), 'actions');
    }

    public function getTwigFilename($actions, $name) {
        return $actions['cache_key'] !== false ? "manapro_filtercontent/{$actions['cache_key']}/$name.twig" : false;
    }

    #region Dependencies
    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }


    #endregion

}