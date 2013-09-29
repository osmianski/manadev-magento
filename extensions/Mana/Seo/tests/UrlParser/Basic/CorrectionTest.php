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
class Mana_Seo_Test_UrlParser_Basic_CorrectionTest extends Mana_Seo_Test_Case {
    public function testNotRecognizedToken() {
        $this->assertParsedUrl('apparel/col.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
            ),
        ));
    }

    public function testEmptyUrlKey() {
        $this->assertParsedUrl('apparel///---/-/-/-/-/-.html', array(
            'route' => 'catalog/category/view',
            'status' => Mana_Seo_Model_ParsedUrl::STATUS_CORRECTION,
            'params' => array(
                'id' => 18,
            ),
        ));
    }
}