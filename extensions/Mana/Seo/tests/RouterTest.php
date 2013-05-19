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
        $this->assertRoute('/electronics.html', array('result' => true));
    }

    public function testCategoryUrlWithOneAppliedValue() {
        $this->assertRoute('/electronics/black.html', array('result' => true));
    }

    public function assertRoute($path, $expected) {
        $request = Mage::app()->getRequest();
        $request
            ->setPathInfo($path)
            ->setDispatched(false);
        $router = new Mana_Seo_Router();
        $result = $router->match($request);

        $this->assertEquals($expected['result'], $result);

    }
}