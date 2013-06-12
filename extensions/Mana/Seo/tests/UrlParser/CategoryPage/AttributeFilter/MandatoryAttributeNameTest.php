<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * AMD value is marked in database as required to use attribute name
 * Contract Ratio attribute is also marked so in Layered Navigation settings
 */
class Mana_Seo_Test_UrlParser_CategoryPage_AttributeFilter_MandatoryAttributeNameTest extends Mana_Seo_Test_Case {
    public function testValue() {
        $this->assertParsedUrl('electronics/manufacturer/amd.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_OK,
            'params' => array(
                'id' => 13,
                'manufacturer' => 117,
            ),
        ));
    }

    public function testMultipleValues() {
        $this->assertParsedUrl('electronics/manufacturer/amd-apple.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_OK,
            'params' => array(
                'id' => 13,
                'manufacturer' => '117_29',
            ),
        ));
    }

    public function testOtherValues() {
        $this->assertParsedUrl('electronics/acer-apple.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_OK,
            'params' => array(
                'id' => 13,
                'manufacturer' => '28_29',
            ),
        ));
    }

    public function testOtherValuesUsingMultipleFilterSyntaxInsteadOfMultipleValueSyntax() {
        $this->assertParsedUrl('electronics/acer/apple.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_CORRECTION,
            'params' => array(
                'id' => 13,
                'manufacturer' => '28_29',
            ),
        ));
    }

    public function testOtherValuesWithAttributeName() {
        $this->assertParsedUrl('electronics/manufacturer/acer-apple.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_CORRECTION,
            'params' => array(
                'id' => 13,
                'manufacturer' => '28_29',
            ),
        ));
    }

    public function testAttribute() {
        $this->assertParsedUrl('electronics/contrast-ratio/10000-1.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_OK,
            'params' => array(
                'id' => 13,
                'contrast_ratio' => 106,
            ),
        ));
    }

    public function testAttributeWithoutAttributeName() {
        $this->assertParsedUrl('electronics/10000-1.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_CORRECTION,
            'params' => array(
                'id' => 13,
                'contrast_ratio' => 106,
            ),
        ));
    }
}