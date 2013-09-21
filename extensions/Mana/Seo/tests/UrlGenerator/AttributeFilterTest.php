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
class Mana_Seo_Test_UrlGenerator_AttributeFilterTest extends Mana_Seo_Test_Case  {
    public function testSingleValue() {
        $this->assertGeneratedUrl('apparel/black.html', 'catalog/category/view', array(
            'id' => 18,
            '_query' => array(
                'color' => 24,
            )
        ));
    }

    public function test3rdPartyValue() {
        $this->assertGeneratedUrl('apparel/black.html?abc=a', 'catalog/category/view', array(
            'id' => 18,
            '_query' => array(
                'color' => 24,
                'abc' => 'a',
            )
        ));
    }

    public function testMultipleValue() {
        $this->assertGeneratedUrl('apparel/black-blue.html', 'catalog/category/view', array(
            'id' => 18,
            '_query' => array(
                'color' => '24_25',
            )
        ));
    }

    public function testTwoFilters() {
        $this->assertGeneratedUrl('apparel/dress/black-blue.html', 'catalog/category/view', array(
            'id' => 18,
            '_query' => array(
                'color' => '24_25',
                'shoe_type' => 52,
            )
        ));
    }

    public function testValueWithMandatoryFilterName() {
        $this->assertGeneratedUrl('electronics/manufacturer/amd.html', 'catalog/category/view', array(
            'id' => 13,
            '_query' => array(
                'manufacturer' => 117,
            )
        ));
    }

    public function testMultipleValuesWithMandatoryFilterName() {
        $this->assertGeneratedUrl('electronics/manufacturer/apple-amd.html', 'catalog/category/view', array(
            'id' => 13,
            '_query' => array(
                'manufacturer' => '117_29',
            )
        ));
    }

    public function testOtherValuesInAFilterHavingValueWithMandatoryFilterName() {
        $this->assertGeneratedUrl('electronics/acer-apple.html', 'catalog/category/view', array(
            'id' => 13,
            '_query' => array(
                'manufacturer' => '28_29',
            )
        ));
    }

    public function testAttributeWithMandatoryFilterName() {
        $this->assertGeneratedUrl('electronics/contrast-ratio/10000-1.html', 'catalog/category/view', array(
            'id' => 13,
            '_query' => array(
                'contrast_ratio' => 106,
            )
        ));
    }
}