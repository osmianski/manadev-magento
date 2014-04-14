<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Core_Helper_PageType_Search extends Mana_Core_Helper_PageType  {
    public function getCurrentSuffix() {
        return Mage::getStoreConfig('mana/seo/search_suffix');
    }

    public function getRoutePath() {
        return 'catalogsearch/result/index';
    }
    /**
     * @return bool|string
     */
    public function getConditionLabel() {
        return $this->__('Quick Search Page');
    }

    public function getPageContent() {
        $result = array(
            'meta_title' => Mage::helper('catalogsearch')->__("Search results for: '%s'",
                Mage::helper('catalogsearch')->getEscapedQueryText()),
            'title' => Mage::helper('catalogsearch')->__("Search results for: '%s'",
                Mage::helper('catalogsearch')->getQueryText()),
        );
        return array_merge(parent::getPageContent(), $result);
    }

    public function getPageTypeId() {
        return 'search';
    }
}