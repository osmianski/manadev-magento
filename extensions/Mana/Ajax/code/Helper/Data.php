<?php
/**
 * @category    Mana
 * @package     Mana_Ajax
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for Mana_Ajax module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_Ajax_Helper_Data extends Mage_Core_Helper_Abstract {
    public function processPageWithoutRendering($route, $callback) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        Mage::register('m_current_ajax_callback', $callback);
        if ($core->inAdmin()) {
            Mage::getSingleton('adminhtml/session')->setIsUrlNotice($callback[0]->getFlag('', 'check_url_settings'));
        }
        $this->_forward($route);
    }

    protected function _forward($action, $controller = null, $module = null, array $params = null) {
        if (is_array($action)) {
            $this->_forward(
                $action['action'],
                isset($action['controller']) ? $action['controller'] : null,
                isset($action['module']) ? $action['module'] : null,
                isset($action['params']) ? $action['params'] : null
            );
            return;
        }
        $request = Mage::app()->getRequest();

        $request->initForward();

        if (!is_null($params)) {
            $request->setParams($params);
        }

        if (!is_null($controller)) {
            $request->setControllerName($controller);

            // Module should only be reset if controller has been specified
            if (!is_null($module)) {
                $request->setModuleName($module);
            }
        }

        $request->setActionName($action)
            ->setDispatched(false);
    }

    public function renderBlock($blockName) {
        if ($block = Mage::getSingleton('core/layout')->getBlock($blockName)) {
            return Mage::getSingleton('core/url')->sessionUrlVar($block->toHtml());
        }
        else {
            return '';
        }
    }

    public function getAllowedActions($actionName) {
        $actionNodes = Mage::helper('mana_core')->getSortedXmlChildren(
            Mage::getConfig()->getNode('mana_ajax/allowed_actions'), $actionName);
        if (count($actionNodes)) {
            $result = array();
            foreach ($actionNodes as $actionNode) {
                $result[] = $actionNode->getName();
            }
            return $result;
        }
        else {
            return false;
        }
    }
    protected $_detected = false;
    protected $_enabled = false;
    public function isEnabled() {
        if (!$this->_detected) {
            switch (Mage::getStoreConfig('mana/ajax/mode')) {
                case Mana_Ajax_Model_Mode::OFF:
                    break;
                case Mana_Ajax_Model_Mode::ON_FOR_ALL:
                    $this->_enabled = true;
                    break;
                case Mana_Ajax_Model_Mode::ON_FOR_USERS:
                    $this->_enabled = true;
                    foreach (explode(';', Mage::getStoreConfig('mana/ajax/bots')) as $agent) {
                        if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], trim($agent)) !== false) {
                            $this->_enabled = false;
                            break;
                        }
                    }
                    break;
                default:
                    throw new Exception('Not implemented');
            }
            $this->_detected = true;
        }
        return $this->_enabled;
    }
}