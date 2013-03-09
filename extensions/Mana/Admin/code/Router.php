<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Admin_Router extends Mage_Core_Controller_Varien_Router_Admin {
    public function match(Zend_Controller_Request_Http $request) {
        /* @var $adminPageHelper Mana_Admin_Helper_Page */
        $adminPageHelper = Mage::helper('mana_admin/page');

        $p = $adminPageHelper->getExplodedPath($request);
        $adminModule = (string) Mage::getConfig()->getNode('admin/routers/adminhtml/args/frontName');

        if ($adminPageHelper->getRequestModule($request) != $adminModule) {
            return false;
        }


        if ($adminPageHelper->hasPageController($request)) {
            $controller = $adminPageHelper->getRequestController($request);
            $action = $adminPageHelper->getRequestAction($request);

            $controllerInstance = Mage::getControllerInstance('Mana_Admin_Controller',
                $request, $this->getFront()->getResponse());

            // set values only after all the checks are done
            $request->setModuleName($adminModule);
            $request->setControllerName($controller);
            $request->setActionName($action);
            $request->setControllerModule('mana_admin');

            for ($i = 3, $l = sizeof($p); $i < $l; $i += 2) {
                $request->setParam($p[$i], isset($p[$i + 1]) ? urldecode($p[$i + 1]) : '');
            }

            // dispatch action
            $request->setDispatched(true);
            $controllerInstance->dispatch($action);

            return true;
        }
        else {
            return false;
        }

    }
}