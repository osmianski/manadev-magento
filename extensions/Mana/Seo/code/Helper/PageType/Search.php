<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Seo_Helper_PageType_Search extends Mana_Seo_Helper_PageType  {
    public function getCurrentSuffix() {
        return Mage::getStoreConfig('mana/seo/search_suffix');
    }

    public function getSuffixHistoryType() {
        return Mana_Seo_Model_UrlHistory::TYPE_SEARCH_SUFFIX;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    public function setPage($token) {
        $token->setRoute('catalogsearch/result/index');

        return true;
    }
}