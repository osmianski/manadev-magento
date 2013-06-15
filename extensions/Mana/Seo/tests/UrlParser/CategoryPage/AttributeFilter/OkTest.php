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
class Mana_Seo_Test_UrlParser_CategoryPage_AttributeFilter_OkTest extends Mana_Seo_Test_Case {
    public function testSingleValue() {
        $this->assertParsedUrl('apparel/black.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_OK,
            'params' => array('id' => 18),
            'query' => array(
                'color' => 24,
            ),
        ));
    }

    public function testMultipleValue() {
        $this->assertParsedUrl('apparel/black-blue.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_OK,
            'params' => array('id' => 18),
            'query' => array(
                'color' => '24_25',
            ),
        ));
    }

    public function testTwoFilters() {
        $this->assertParsedUrl('apparel/black-blue/dress.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_OK,
            'params' => array('id' => 18),
            'query' => array(
                'color' => '24_25',
                'shoe_type' => 52,
            ),
        ));
    }

    public function testUnnecessaryAttributeName() {
        $this->assertParsedUrl('apparel/color/black.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_CORRECTION,
            'params' => array('id' => 18),
            'query' => array(
                'color' => 24,
            ),
        ));
    }

    public function testMultipleValueInTwoPlaces() {
        $this->assertParsedUrl('apparel/black/dress/blue.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_CORRECTION,
            'params' => array('id' => 18),
            'query' => array(
                'color' => '24_25',
                'shoe_type' => 52,
            ),
        ));
    }
}