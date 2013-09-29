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
class Mana_Core_Helper_PageType_CmsPage extends Mana_Core_Helper_PageType  {
    public function getCurrentSuffix() {
        return Mage::getStoreConfig('mana/seo/cms_page_suffix');
    }

    public function getRoutePath() {
        return 'cms/page/view';
    }
}