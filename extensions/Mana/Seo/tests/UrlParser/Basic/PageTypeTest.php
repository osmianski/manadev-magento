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
class Mana_Seo_Test_UrlParser_Basic_PageTypeTest extends Mana_Seo_Test_Case {
    public function testCategory() {
        $this->assertParsedUrl('/apparel.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Helper_UrlParser::STATUS_OK,
            'params' => array(
                'id' => 18
            ),
        ));
    }

    public function testSubcategory() {
        $this->assertParsedUrl('/apparel/shoes.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Helper_UrlParser::STATUS_OK,
            'params' => array(
                'id' => 5
            ),
        ));
    }
}