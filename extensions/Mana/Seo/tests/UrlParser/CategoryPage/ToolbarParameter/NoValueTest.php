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
class Mana_Seo_Test_UrlParser_CategoryPage_ToolbarParameter_NoValueTest extends Mana_Seo_Test_Case {
    public function testShow() {
        $this->assertParsedUrl('/apparel/show.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Helper_UrlParser::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
            ),
        ));
    }

    public function testMode() {
        $this->assertParsedUrl('/apparel/mode.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Helper_UrlParser::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
            ),
        ));
    }

    public function testPage() {
        $this->assertParsedUrl('/apparel/page.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Helper_UrlParser::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
            ),
        ));
    }

    public function testSortBy() {
        $this->assertParsedUrl('/apparel/sort-by.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Helper_UrlParser::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
            ),
        ));
    }

    public function testSortDirection() {
        $this->assertParsedUrl('/apparel/sort-direction.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Helper_UrlParser::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
            ),
        ));
    }
}