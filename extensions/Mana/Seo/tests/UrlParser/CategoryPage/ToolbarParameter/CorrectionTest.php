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
class Mana_Seo_Test_UrlParser_CategoryPage_ToolbarParameter_CorrectionTest extends Mana_Seo_Test_Case {
    public function testShow() {
        $this->assertParsedUrl('apparel/show/qq.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
            ),
        ));
    }

    public function testMode() {
        $this->assertParsedUrl('apparel/mode/cheat.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
            ),
        ));
    }

    public function testPage() {
        $this->assertParsedUrl('apparel/page/two.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
            ),
        ));
    }

    public function testSortBy() {
        $this->assertParsedUrl('apparel/sort-by/color.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
            ),
        ));
    }

    public function testSortDirection() {
        $this->assertParsedUrl('apparel/sort-direction/descent.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
            ),
        ));
    }
}