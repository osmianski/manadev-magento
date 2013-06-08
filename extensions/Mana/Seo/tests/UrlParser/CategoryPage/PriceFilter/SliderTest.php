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
class Mana_Seo_Test_UrlParser_CategoryPage_PriceFilter_SliderTest extends Mana_Seo_Test_Case {
    public function testNoValues() {
        $this->assertParsedUrl('apparel/price.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
            ),
        ));
    }

    public function testOneValue() {
        $this->assertParsedUrl('apparel/price/0.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
            ),
        ));
    }

    public function testNonNumericValues() {
        $this->assertParsedUrl('apparel/price/a-1.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
            ),
        ));
    }

    public function testTwoValues() {
        $this->assertParsedUrl('apparel/price/100-200.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_OK,
            'params' => array(
                'id' => 18,
                'price' => '100,200',
            ),
        ));
    }

    public function testFirstValueIsGreaterThanSecondValue() {
        $this->assertParsedUrl('apparel/price/200-100.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
                'price' => '100,200',
            ),
        ));
    }

    public function testNegativeValues() {
        $this->assertParsedUrl('apparel/price/-200--100.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
                'price' => '-200,-100',
            ),
        ));
    }


    public function testOldSchema() {
        $this->assertParsedUrl('apparel/where/price/100,200.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_OBSOLETE,
            'params' => array(
                'id' => 18,
                'price' => '100,200',
            ),
        ));
    }

    public function testOldSchemaJavascriptTemplate() {
        $this->assertParsedUrl('apparel/where/price/__0__,__1__.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
            ),
        ));
    }

}