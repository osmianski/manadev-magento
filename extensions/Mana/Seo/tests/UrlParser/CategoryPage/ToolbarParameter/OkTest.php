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
class Mana_Seo_Test_UrlParser_CategoryPage_ToolbarParameter_OkTest extends Mana_Seo_Test_Case {
    public function testShowAll() {
        $this->assertParsedUrl('apparel/show/all.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_OK,
            'params' => array(
                'id' => 18,
                'limit' => 'all',
            ),
        ));
    }

    public function testShow15() {
        $this->assertParsedUrl('apparel/show/15.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_OK,
            'params' => array(
                'id' => 18,
                'limit' => '15',
            ),
        ));
    }

    public function testGridMode() {
        $this->assertParsedUrl('apparel/mode/grid.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_OK,
            'params' => array(
                'id' => 18,
                'mode' => 'grid',
            ),
        ));
    }

    public function testPage2() {
        $this->assertParsedUrl('apparel/page/2.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_OK,
            'params' => array(
                'id' => 18,
                'p' => 2,
            ),
        ));
    }

    public function testSortByPrice() {
        $this->assertParsedUrl('apparel/sort-by/price.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_OK,
            'params' => array(
                'id' => 18,
                'order' => 'price',
            ),
        ));
    }

    public function testDescendingSortDirection() {
        $this->assertParsedUrl('apparel/sort-direction/descending.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_OK,
            'params' => array(
                'id' => 18,
                'dir' => 'desc',
            ),
        ));
    }
}