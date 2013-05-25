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
class Mana_Seo_Test_UrlGenerator extends Mana_Core_Test_Case {
    protected function assertCategoryUrl($expected, $query = '') {
        $params = array(
            '__route' => 'catalog/category/view',
            '_direct' => 'apparel.html',
        );
        $this->assertUrl($expected, $params, $query);
    }

    protected function assertOptionPageBasedOnCategoryUrl($expected, $query = '') {
        $params = array(
            '__route' => 'mana_attributepage/option/category',
            '_direct' => 'apparel/red.html',
        );
        $this->assertUrl($expected, $params, $query);
    }

    protected function assertUrl($expected, $params, $query = '') {
        if ($query) {
            parse_str($query, $parsedQuery);
            $params['query'] = $parsedQuery;
        }
        $url = Mage::getUrl('*/*/*', array_merge(array(
                '_m_escape' => '',
                '_use_rewrite' => true,
                '_nosid' => true,
            )
        , $params));
        $relativeUrl = substr($url, strlen(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK)));
        $this->assertEquals($expected, $relativeUrl);
    }
}