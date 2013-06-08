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
class Mana_Seo_Test_UrlParser_CategoryPage_AttributeFilter_OldParameterUrlKeyTest extends Mana_Seo_Test_Case {
    public function testSingleValue() {
        $this->assertParsedUrl('apparel/black-old.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_OBSOLETE,
            'params' => array(
                'id' => 18,
                'color' => 24,
            ),
        ));
    }

    public function testMultipleValue() {
        $this->assertParsedUrl('apparel/black-old-blue-old.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_OBSOLETE,
            'params' => array(
                'id' => 18,
                'color' => '24_25',
            ),
        ));
    }

    public function testTwoFilters() {
        $this->assertParsedUrl('apparel/black-old-blue-old-dress-old.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_OBSOLETE,
            'params' => array(
                'id' => 18,
                'color' => '24_25',
                'shoe_type' => 52,
            ),
        ));
    }

    public function testUnnecessaryAttributeName() {
        $this->assertParsedUrl('apparel/color-old/black.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
                'color' => 24,
            ),
        ));
    }
}