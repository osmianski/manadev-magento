<?php
/**
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for Mana_AttributePage module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_AttributePage_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getOptionPageSuffix() {
        return '.html';
    }

    public function getAttributePageSuffix() {
        return '.html';
    }

    public function useSolrForNavigation() {
        if (!Mage::helper('core')->isModuleEnabled('Enterprise_Search')) {
            return false;
        }
        /* @var $helper Enterprise_Search_Helper_Data */
        $helper = Mage::helper('enterprise_search');

        return $helper->getIsEngineAvailableForNavigation();
    }

    public function getAttributePageMenuHtml() {
        /* @var $_block Mana_AttributePage_Block_Navigation */
        $_block = Mage::getSingleton('core/layout')->getBlockSingleton('mana_attributepage/navigation');
        return $_block->getHtml();
    }
}