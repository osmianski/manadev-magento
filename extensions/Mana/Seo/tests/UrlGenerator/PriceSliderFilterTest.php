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
class Mana_Seo_Test_UrlGenerator_PriceSliderFilterTest extends Mana_Seo_Test_Case  {
    public function testTwoValues() {
        $this->assertGeneratedUrl('apparel/price/100-200.html', 'catalog/category/view', array(
            'id' => 18,
            '_query' => array(
                'price' => '100,200',
            )
        ));
    }

    public function testNegativeValues() {
        $this->assertGeneratedUrl('apparel/price/-200--100.html', 'catalog/category/view', array(
            'id' => 18,
            '_query' => array(
                'price' => '-200,-100',
            )
        ));
    }
}