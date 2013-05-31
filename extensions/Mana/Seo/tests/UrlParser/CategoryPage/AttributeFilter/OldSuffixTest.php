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
class Mana_Seo_Test_UrlParser_CategoryPage_AttributeFilter_OldSuffixTest extends Mana_Seo_Test_Case {
    public function testSingleValue() {
        $this->assertParsedUrl('/apparel/black', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Helper_UrlParser::STATUS_OBSOLETE,
            'params' => array(
                'id' => 18,
                'color' => 24,
            ),
        ));
    }

    public function testMultipleValue() {
        $this->assertParsedUrl('/apparel/black-blue', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Helper_UrlParser::STATUS_OBSOLETE,
            'params' => array(
                'id' => 18,
                'color' => '24_25',
            ),
        ));
    }

    public function testTwoFilters() {
        $this->assertParsedUrl('/apparel/black-blue-dress', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Helper_UrlParser::STATUS_OBSOLETE,
            'params' => array(
                'id' => 18,
                'color' => '24_25',
                'shoe_type' => 52,
            ),
        ));
    }
}