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
class Mana_Seo_Test_RouterTest extends Mana_Core_Test_Case {
    public function testCategoryUrl() {
        $this->assertRoute(array(), '/electronics.html');
    }

    public function assertRoute($expected, $path) {
    }
}