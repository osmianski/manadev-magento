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
        $this->assertParsedUrl('apparel.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_OK,
            'params' => array(
                'id' => 18
            ),
        ));
    }

    public function testSubcategory() {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        $this->assertParsedUrl($core->isEnterpriseUrlRewriteInstalled() ? 'shoes.html' : 'apparel/shoes.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_OK,
            'params' => array(
                'id' => 5
            ),
        ));
    }
}