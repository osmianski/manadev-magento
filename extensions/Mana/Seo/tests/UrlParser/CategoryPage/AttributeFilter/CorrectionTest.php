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
class Mana_Seo_Test_UrlParser_CategoryPage_AttributeFilter_CorrectionTest extends Mana_Seo_Test_Case {
    public function testNoValues() {
        $this->assertParsedUrl('/apparel/color.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Helper_UrlParser::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
            ),
        ));
    }

    public function testUnnecessaryAttributeName() {
        $this->assertParsedUrl('/apparel/color/black.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Helper_UrlParser::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
                'color' => 24,
            ),
        ));
    }

    public function testMultipleValueInTwoPlaces() {
        $this->assertParsedUrl('/apparel/black-dress-blue.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Helper_UrlParser::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
                'color' => '24_25',
                'shoe_type' => 52,
            ),
        ));
    }
}