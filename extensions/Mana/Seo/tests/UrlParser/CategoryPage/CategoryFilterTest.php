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
class Mana_Seo_Test_UrlParser_CategoryPage_CategoryTest extends Mana_Seo_Test_Case {
    public function testNoValues() {
        $this->assertParsedUrl('apparel/category.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_CORRECTION,
            'params' => array('id' => 18),
        ));
    }

    public function testUnnecessaryFilterName() {
        $this->assertParsedUrl('apparel/category/shoes.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_REDIRECT,
            'params' => array('id' => 5),
        ));
    }

    public function testMultipleValues() {
        $this->assertParsedUrl('apparel/category/shoes-shirts.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_CORRECTION,
            'params' => array('id' => 18),
        ));
    }

    public function testOldSchema() {
        $this->assertParsedUrl('apparel/where/category/shoes.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_OBSOLETE,
            'params' => array('id' => 18),
            'query' => array(
                'cat' => 5,
            ),
        ));
    }

}