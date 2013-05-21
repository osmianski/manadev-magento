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
        $this->assertRoute('/electronics.html', array(
            'result' => true,
            'route' => 'catalog/category/view',
            'params' => array(
                'id' => 13
            ),
        ));
    }

    public function testCategoryUrlWithOneAppliedValue() {
        $this->assertRoute('/electronics/black.html', array(
            'result' => true,
            'route' => 'catalog/category/view',
            'params' => array(
                'id' => 13,
                'color' => '24'
            ),
        ));
        $this->assertRoute('/electronics/color/black.html', array(
            'result' => true,
            'route' => 'catalog/category/view',
            'params' => array(
                'id' => 13,
                'color' => '24'
            ),
        ));
        $this->assertRoute('/electronics/where/color/black.html', array(
            'result' => true,
            'route' => 'catalog/category/view',
            'params' => array(
                'id' => 13,
                'color' => '24'
            ),
        ));
    }

    public function testTemp() {
        $this->assertRoute('/electronics/where/color/black.html', array(
            'result' => true,
            'redirect' => 'electronics/black.html',
        ));
    }

    public function testCategoryUrlWithTwoAppliedValues() {
        $this->assertRoute('/electronics/black-blue.html', array(
            'result' => true,
            'route' => 'catalog/category/view',
            'params' => array(
                'id' => 13,
                'color' => '24_25'
            ),
        ));
        $this->assertRoute('/electronics/blue-black.html', array(
            'result' => true,
            'route' => 'catalog/category/view',
            'params' => array(
                'id' => 13,
                'color' => '24_25'
            ),
        ));
    }

    public function assertRoute($path, $expected) {
        $request = Mage::app()->getRequest();
        $request
            ->setPathInfo($path)
            ->setDispatched(false);
        $front = new Mage_Core_Controller_Varien_Front();
        $router = new Mana_Seo_Router();
        $router->setFront($front);
        $result = $router->match($request);

        if (isset($expected['result'])) {
            $this->assertEquals($expected['result'], $result);
        }
        $match = $router->getLastMatch();
        if (isset($expected['route'])) {
            $this->assertInstanceOf('Mana_Seo_Model_Context', $match);
            $expectedRoute = explode('/', $expected['route']);
            $this->assertEquals($expectedRoute[0], $request->getModuleName());
            $this->assertEquals($expectedRoute[1], $request->getControllerName());
            $this->assertEquals($expectedRoute[2], $request->getActionName());
        }
        if (isset($expected['params'])) {
            foreach ($expected['params'] as $key => $value) {
                $this->assertEquals($value, $request->getParam($key));
            }
        }
        if (isset($expected['redirect'])) {
            $this->assertEquals($expected['redirect'], $match);
        }
    }
}