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
class Mana_Seo_Test_UrlGenerator_ToolbarParameterFilterTest extends Mana_Seo_Test_Case  {
    public function testShowAll() {
        $this->assertGeneratedUrl('apparel/show/all.html', 'catalog/category/view', array(
            'id' => 18,
            '_query' => array(
                'limit' => 'all',
            )
        ));
    }

    public function testShow15() {
        $this->assertGeneratedUrl('apparel/show/15.html', 'catalog/category/view', array(
            'id' => 18,
            '_query' => array(
                'limit' => '15',
            )
        ));
    }

    public function testGridMode() {
        $this->assertGeneratedUrl('apparel/mode/grid.html', 'catalog/category/view', array(
            'id' => 18,
            '_query' => array(
                'mode' => 'grid',
            )
        ));
    }

    public function testPage2() {
        $this->assertGeneratedUrl('apparel/page/2.html', 'catalog/category/view', array(
            'id' => 18,
            '_query' => array(
                'p' => 2,
            )
        ));
    }

    public function testSortByPrice() {
        $this->assertGeneratedUrl('apparel/sort-by/price.html', 'catalog/category/view', array(
            'id' => 18,
            '_query' => array(
                'order' => 'price',
            )
        ));
    }

    public function testDescendingSortDirection() {
        $this->assertGeneratedUrl('apparel/sort-direction/desc.html', 'catalog/category/view', array(
            'id' => 18,
            '_query' => array(
                'dir' => 'desc',
            )
        ));
    }
}