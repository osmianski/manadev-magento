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
 * Specifies cases when canonical URL should NOT be rendered on page/2, page/3 and further pages
 */
class Mana_Seo_Model_Source_Canonical_HideWhenPagerIsUsed extends Mana_Core_Model_Source_Abstract {
    // render canonical URL no matter if page/2 is in URL
    const NEVER  = '';

    // hide canonical URL on `category/page/N` pages. The idea is that search engine will fetch `category` page, then use rel=next links
    // to fetch `category/page/N` pages and "add" their content to `category` page
    const ON_NON_FILTERED_PAGES_ONLY = 'non_filtered';

    // hide canonical URL on `category/color/red/page/N` pages. The idea is that search engine will fetch `category` page, then use rel=next links
    // to fetch `category/color/red/page/N` pages and "add" their content to `category/color/red` page
    const ON_ALL_PAGES_EXCEPT_HAVING_TOOLBAR_PARAMETERS = 'all';

    // additional options for dealing with `category/color/red/sort-by/price/page/N` pages are not needed as such pages should have
    // `category/color/red` canonical URL


    protected function _getAllOptions() {
        return array(
            array('value' => self::NEVER, 'label' => Mage::helper('mana_seo')->__('Never')),
            array('value' => self::ON_NON_FILTERED_PAGES_ONLY, 'label' => Mage::helper('mana_seo')->__('On non-filtered pages only (`category/page/N`)')),
            array('value' => self::ON_ALL_PAGES_EXCEPT_HAVING_TOOLBAR_PARAMETERS, 'label' => Mage::helper('mana_seo')->__('On all pages except having toolbar parameters (`category/filter/value/page/N`')),
        );
    }
}