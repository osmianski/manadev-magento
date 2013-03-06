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
class Mana_Admin_Helper_Page extends Mage_Core_Helper_Abstract {
    protected $_xmlConfig;
    protected $_pageLayoutHandles;

    /**
     * @param Zend_Controller_Request_Http $request
     * @return SimpleXMLElement
     */
    public function getPageLayout(Zend_Controller_Request_Http $request) {
        $handles = $this->getPageLayoutHandles();
        $pageName = 'adminhtml_'.$this->getRequestController($request) . '_' . $this->getRequestAction($request);
        return isset($handles[$pageName]) ? $handles[$pageName] : null;
    }

    public function getGridLayout(Zend_Controller_Request_Http $request) {
        $controller = $this->getRequestController($request);
        $xml = $this->getLayoutXml();
        $actions = $xml->xpath('//action[@method="setGridController" and value="' . $controller . '"]');
        if (count($actions) != 1) {
            return null;
        }
        /* @var $action Mage_Core_Model_Layout_Element */
        $action = $actions[0];
        $block = $action->getParent();
        $block = (string)$block['name'];

        for($handle = $action->getParent(); $handle->getParent() != $xml; $handle = $handle->getParent());
        $controller = explode('_', $handle->getName());
        $module = array_shift($controller);
        if ($module == 'adminhtml') {
            $module = (string)Mage::getConfig()->getNode('admin/routers/adminhtml/args/frontName');
        }
        $action = array_pop($controller);
        $controller = implode('_', $controller);
        $route = compact('module', 'controller', 'action');
        return compact('route', 'block');
    }

    public function getRequestModule(Zend_Controller_Request_Http $request) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        return $core->getRequestModule($request);
    }

    public function getRequestController(Zend_Controller_Request_Http $request) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');
        return $core->getRequestController($request);
    }

    public function getRequestAction(Zend_Controller_Request_Http $request) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        return $core->getRequestAction($request);
    }

    public function getExplodedPath(Zend_Controller_Request_Http $request) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        return $core->getExplodedPath($request);
    }

    /**
     * Loads Admin Panel layout XMLs
     *
     * @return Mage_Core_Model_Layout_Element
     */
    public function getLayoutXml() {
        if (!$this->_xmlConfig) {
            /* @var $layout Mage_Core_Model_Layout */
            $layout = Mage::getModel('core/layout');
            $update = $layout->getUpdate();
            $this->_xmlConfig = $update->getFileLayoutUpdatesXml('adminhtml',
                (string)Mage::getConfig()->getNode('stores/admin/design/package/name'),
                (string)Mage::getConfig()->getNode('stores/admin/design/theme/default'));
        }

        return $this->_xmlConfig;
    }

    public function getPageLayoutHandles() {
        if (empty($this->_pageLayoutHandles)) {
            if ($layoutHandlesArr = $this->getLayoutXml()->xpath('/*/*[@type="page"]')) {
                foreach ($layoutHandlesArr as $node) {
                    $this->_pageLayoutHandles[$node->getName()] = $node->getName();
                }
            }
        }

        return $this->_pageLayoutHandles;
    }

    /**
     * @param Mage_Core_Block_Abstract $block
     * @return string
     */
    public function getActionHtml($block) {
        $html = '';

        $actions = $block->getChildGroup('actions');
        uasort($actions, array($this, 'compareBySortOrder'));
        foreach ($actions as $alias => $action) {
            /* @var $action Mana_Admin_Block_Action */

            $params = $action->getData();
            $this->copyParam($params, 'title', 'label');
            $action->setData($params);

            $html .= $block->getChildHtml($alias);
        }

        return $html;
    }

    #region Parameter handling
    public function removeParam(&$params, $key) {
        if (isset($params[$key])) {
            unset($params[$key]);
        }

        return $this;
    }

    public function copyParam(&$params, $sourceKey, $targetKey) {
        if (isset($params[$sourceKey])) {
            $params[$targetKey] = $params[$sourceKey];
        }

        return $this;
    }

    public function renameParam(&$params, $sourceKey, $targetKey) {
        return $this
            ->copyParam($params, $sourceKey, $targetKey)
            ->removeParam($params, $sourceKey);
    }

    /**
     * @param Varien_Object $a
     * @param Varien_Object $b
     * @return int
     */
    public function compareBySortOrder($a, $b) {
        if ($a->getData('sort_order') < $b->getData('sort_order')) return -1;
        if ($a->getData('sort_order') > $b->getData('sort_order')) return 1;

        return 0;
    }

    #endregion

}