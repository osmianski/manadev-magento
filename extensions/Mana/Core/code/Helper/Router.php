<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Core_Helper_Router extends Mage_Core_Helper_Abstract {
    public function forward($route, $request = null, $params = null, $query = null) {
        if (is_array($route)) {
            $this->forward(
                $route['route'],
                isset($action['request']) ? $action['request'] : null,
                isset($action['params']) ? $action['params'] : null,
                isset($action['query']) ? $action['query'] : null
            );

            return $this;
        }
        if (!$request) {
            $request = Mage::app()->getRequest();
        }

        $request->initForward();

        if (!is_null($params)) {
            $request->setParams($params);
        }

        list($module, $controller, $action) = explode('/', $route);

        if ($controller != '*') {
            $request->setControllerName($controller);

        }

        if ($module != '*') {
            $request->setModuleName($module);
        }

        if ($action != '*') {
            $request->setActionName($action);
        }

        $request->setDispatched(false);

        if (!is_null($query)) {
            $_GET = $query;
        }

        return $this;
    }

    public function changePath($path, $request = null) {
        if (!$request) {
            $request = Mage::app()->getRequest();
        }

        if ($path === '') {
            $path = '/';
        }
        $request
            ->setPathInfo($path)
            ->setModuleName(null)
            ->setControllerName(null)
            ->setActionName(null)
            ->setDispatched(false);

        return $this;
    }

    public function processWithoutRendering($target, $method) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        Mage::register('m_response_callback', array($target, $method));
        if ($core->inAdmin() && $target instanceof Mage_Adminhtml_Controller_Action) {
            /* @var $adminSession Mage_Adminhtml_Model_Session */
            $adminSession = Mage::getSingleton('adminhtml/session');
            $adminSession->setDataUsingMethod('is_url_notice', $target->getFlag('', 'check_url_settings'));
        }
        Mage::app()->getFrontController()->setDataUsingMethod('no_render', true);

        return $this;
    }

}