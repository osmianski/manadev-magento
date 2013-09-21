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
class Mana_Seo_Test_UrlGenerator_PriceListFilterTest extends Mana_Seo_Test_Case  {
    protected function setUp() {
        $this->_setAttributeUrlFilterDisplay('price', 'css_checkboxes');
    }

    protected function tearDown() {
        $this->_setAttributeUrlFilterDisplay('price', 'slider');
    }

    public function testTwoValues() {
        $this->assertGeneratedUrl('apparel/price/100-200.html', 'catalog/category/view', array(
            'id' => 18,
            '_query' => array(
                'price' => '2,100',
            )
        ));
    }

    public function testNegativeValues() {
        $this->assertGeneratedUrl('apparel/price/-200--100.html', 'catalog/category/view', array(
            'id' => 18,
            '_query' => array(
                'price' => '-1,100',
            )
        ));
    }
}