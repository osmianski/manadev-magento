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
class Mana_Seo_Test_UrlTest extends Mana_Core_Test_Case {
    public function testCategoryUrl() {
        $this->assertUrl('electronics.html', 'electronics.html');
    }

    public function testTemp() {
        $this->assertUrl('electronics/black.html', 'electronics.html', array(
            'color' => '24',
        ));
    }
    protected function assertUrl($expected, $directUrl, $query = array()) {
        $url = Mage::getUrl('*/*/*', array(
                '_direct' => $directUrl,
                '_m_escape' => '',
                '_use_rewrite' => true,
                '_query' => $query,
                '_nosid' => true,
            )
        );
        $relativeUrl = substr($url, strlen(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK)));
        $this->assertEquals($expected, $relativeUrl);
    }
}