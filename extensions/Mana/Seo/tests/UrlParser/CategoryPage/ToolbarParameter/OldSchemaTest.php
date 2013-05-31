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
class Mana_Seo_Test_UrlParser_CategoryPage_ToolbarParameter_OldSchemaTest extends Mana_Seo_Test_Case {
    public function testShowAll() {
        $this->assertParsedUrl('/apparel/where/limit/all.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Helper_UrlParser::STATUS_OBSOLETE,
            'params' => array(
                'id' => 18,
                'limit' => 'all',
            ),
        ));
    }

    public function testShow15() {
        $this->assertParsedUrl('/apparel/where/limit/15.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Helper_UrlParser::STATUS_OBSOLETE,
            'params' => array(
                'id' => 18,
                'limit' => '15',
            ),
        ));
    }

    public function testGridMode() {
        $this->assertParsedUrl('/apparel/where/mode/grid.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Helper_UrlParser::STATUS_OBSOLETE,
            'params' => array(
                'id' => 18,
                'mode' => 'grid',
            ),
        ));
    }

    public function testPage2() {
        $this->assertParsedUrl('/apparel/where/p/2.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Helper_UrlParser::STATUS_OBSOLETE,
            'params' => array(
                'id' => 18,
                'p' => 2,
            ),
        ));
    }

    public function testSortByPrice() {
        $this->assertParsedUrl('/apparel/where/order/price.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Helper_UrlParser::STATUS_OBSOLETE,
            'params' => array(
                'id' => 18,
                'order' => 'price',
            ),
        ));
    }

    public function testDescendingSortDirection() {
        $this->assertParsedUrl('/apparel/where/dir/desc.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Helper_UrlParser::STATUS_OBSOLETE,
            'params' => array(
                'id' => 18,
                'dir' => 'desc',
            ),
        ));
    }
}