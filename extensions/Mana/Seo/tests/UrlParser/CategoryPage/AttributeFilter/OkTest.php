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
        $this->assertParsedUrl('/apparel/black.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Helper_UrlParser::STATUS_OK,
            'params' => array(
                'id' => 18,
                'color' => 24,
            ),
        ));
    }

    public function testMultipleValue() {
        $this->assertParsedUrl('/apparel/black-blue.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Helper_UrlParser::STATUS_OK,
            'params' => array(
                'id' => 18,
                'color' => '25_24',
            ),
        ));
    }

    public function testTwoFilters() {
        $this->assertParsedUrl('/apparel/black-blue-dress.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Helper_UrlParser::STATUS_OK,
            'params' => array(
                'id' => 18,
                'color' => '25_24',
                'shoe_type' => 52,
            ),
        ));
    }
}