<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterAjax
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterAjax_Router extends Mage_Core_Controller_Varien_Router_Abstract  {
    protected $_route;

    public function match(Zend_Controller_Request_Http $request) {
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }

        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        if ($core->getRoutePath() != '//') {
            return false;
        }

        /* @var $routerHelper Mana_Core_Helper_Router */
        $routerHelper = Mage::helper('mana_core/router');

        $path = ltrim($request->getPathInfo(), '/');
        $urlKey = Mage::getStoreConfig('mana/ajax/url_key_filter');
        $routeSeparator = Mage::getStoreConfig('mana/ajax/route_separator_filter');
        if ($core->startsWith($path, $urlKey . '/') && ($pos = strpos($path, '/'. $routeSeparator.'/')) > strlen($urlKey .'/')) {
            $this->_route = substr($path, strlen($urlKey . '/'), $pos - strlen($urlKey . '/'));
            $path = substr($path, $pos + strlen('/' . $routeSeparator));
            $routerHelper
                ->changePath($path)
                ->processWithoutRendering($this, 'render');
            $baseUrl = parse_url(Mage::getUrl(null, array('_nosid' => true)));

            Mage::register('m_original_request_uri', $_SERVER['REQUEST_URI']);
            $_SERVER['REQUEST_URI'] = $baseUrl['path'] . ($path ? ltrim($path, '/') : '/')
                . (($queryPos = strpos($_SERVER['REQUEST_URI'], '?')) !== false ? substr($_SERVER['REQUEST_URI'], $queryPos) : '');

            Mage::register('manapro_filterajax_request', 1);
            $this->getCatalogSession()->setData('manapro_filterajax_request', 1);
            if ($core->isEnterpriseUrlRewriteInstalled()) {
                $this->_getRequestRewriteController()->rewrite();
            }
            else {
                Mage::getModel('core/url_rewrite')->rewrite();
            }
        }
        return false;
    }

    public function render() {
        /* @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getSingleton('core/layout');

        /* @var $layoutHelper Mana_Core_Helper_Layout */
        $layoutHelper = Mage::helper('mana_core/layout');

        /* @var $js Mana_Core_Helper_Js */
        $js = Mage::helper('mana_core/js');

        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        $response = array('blocks' => array());
        $sections = array();
        if ($blocks = $layout->getBlock('m_ajax_update')) {
            $key = 'rendered_but_not_sent_blocks';
            if ($blocks->getData($key)) {
                foreach (explode(',', $blocks->getData($key)) as $blockName) {
                    $layoutHelper->renderBlock($blockName);
                }
            }

            $key = $this->_route == $core->getRoutePath() . $core->getRouteParams()
                ? 'updated_blocks_if_parameter_changed'
                : 'updated_blocks_if_page_changed';

            if ($blocks->hasData($key)) {
                foreach (explode(',', $blocks->getData($key)) as $blockName) {
                    if ($html = $layoutHelper->renderBlock($blockName)) {
                        $response['blocks'][$js->getClientSideBlockName($blockName)] = count($sections);
                        $sections[] = $html;
                    }
                }
            }
        }

        $response['config'] = $js->getConfig();
        if ($headBlock = $layout->getBlock('head')) {
            /* @var $headBlock Mage_Page_Block_Html_Head */
            $headBlock->getTitle();
            $response['title'] = $headBlock->getData('title');
        }
        array_unshift($sections, json_encode($response));
        Mage::app()->getResponse()->setBody(implode($js->getSectionSeparator(), $sections));
    }

    #region Dependencies

    /**
     * @return Mage_Catalog_Model_Session
     */
    public function getCatalogSession() {
        Mage::getSingleton('core/session', array('name' => 'frontend'));
        return Mage::getSingleton('catalog/session');
    }

    protected function _getRequestRewriteController()
    {
        $className = (string)Mage::getConfig()->getNode('global/request_rewrite/model');

        return Mage::getSingleton('core/factory')->getModel($className, array(
            'routers' => $this->getFront()->getRouters(),
        ));
    }
    #endregion
}