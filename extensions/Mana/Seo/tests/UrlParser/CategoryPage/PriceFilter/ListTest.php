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
class Mana_Seo_Test_UrlParser_CategoryPage_PriceFilter_ListTest extends Mana_Seo_Test_Case {
    public function testTwoValues() {
        $this->assertParsedUrl('apparel/price/100-200.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_OK,
            'params' => array(
                'id' => 18,
                'price' => '1,100',
            ),
        ));
    }

    public function testFirstValueIsGreaterThanSecondValue() {
        $this->assertParsedUrl('apparel/price/200-100.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
                'price' => '1,100',
            ),
        ));
    }

    public function testNegativeValues() {
        $this->assertParsedUrl('apparel/price/-200--100.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
                'price' => '-2,100',
            ),
        ));
    }


    public function testOldSchema() {
        $this->assertParsedUrl('apparel/where/price/1,100.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_OBSOLETE,
            'params' => array(
                'id' => 18,
                'price' => '1,100',
            ),
        ));
    }

}