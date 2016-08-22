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
class Mana_InfiniteScrolling_Router extends Mage_Core_Controller_Varien_Router_Abstract  {
    protected $_route;
    protected $_page;
    protected $_limit;

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

        // our accepted URL structure is
        //      ajax/infinite-scrolling/cms/index/index/id/13/page/11/limit/10/requested-url/price-0-2476.4

        $path = ltrim($request->getPathInfo(), '/');

        // certain parts in this URL are static. We put them into variables below. Here are their places
        // in the same URL:
        //      {$urlKey}cms/index/index/id/13{$pageSeparator}11{$limitSeparator}10{$routeSeparator}price-0-2476.4

        $urlKey = Mage::getStoreConfig('mana/ajax/url_key_infinitescrolling') . '/';
        $pageSeparator = '/' . Mage::getStoreConfig('mana/ajax/page_separator') . '/';
        $limitSeparator = '/' . Mage::getStoreConfig('mana/ajax/limit_separator') . '/';
        $routeSeparator = '/' . Mage::getStoreConfig('mana/ajax/route_separator_filter') . '/';
        $pageVarSeparator = '/pageVarName/';
        $limitVarSeparator = '/limitVarName/';

        $regex = preg_quote($urlKey, '/') . '(.+)' . preg_quote($pageSeparator, '/') . '([0-9]+)' .
            preg_quote($limitSeparator, '/') . '([0-9]+)' . preg_quote($pageVarSeparator, '/') . '(.*)' .
            preg_quote($limitVarSeparator, '/') . '(.*)' . preg_quote($routeSeparator, '/') . '(.*)';
        if (preg_match("/$regex/", $path, $matches)) {
            // fetch all URL dynamic parts into object fields which are used later in render method
            //      ajax/infinite-scrolling/{$route}/page/{page}/limit/{$limit}/requested-url/{path}

            $this->_route = $matches[1];
            $this->_page = $matches[2];
            $this->_limit = $matches[3];
            $pageVarName = $matches[4];
            $limitVarName = $matches[5];
            $path = $matches[6];

            // let all further Magento logic think that we just received $path. Prevent full page
            // Magento rendering and instead call render() method of this class
            $routerHelper
                ->changePath($path)
                ->processWithoutRendering($this, 'render');

            $_GET = array_merge($_GET, array($pageVarName => $this->_page, $limitVarName => $this->_limit));

            $baseUrl = parse_url(Mage::getUrl(null, array('_nosid' => true)));
            Mage::register('m_original_request_uri', $_SERVER['REQUEST_URI']);
            $_SERVER['REQUEST_URI'] = $baseUrl['path'] . ($path ? ltrim($path, '/') : '/')
                . (($queryPos = strpos($_SERVER['REQUEST_URI'], '?')) !== false ? substr($_SERVER['REQUEST_URI'], $queryPos) : '');

            // set flags for special processing of AJAX requests (for instance by FPC)
            Mage::register('manapro_filterajax_request', 1);
            $this->getCatalogSession()->setData('manapro_filterajax_request', 1);

            // let standard Magento translate SEO friendly category URLs into standard
            // {module}/{controller}/{action}/{params} from
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

        $html = '';
        /* @var $engineBlock Mana_InfiniteScrolling_Block_Engine */
        /* @var $listBlock Mage_Catalog_Block_Product_List */
        if (($engineBlock = $layout->getBlock('infinitescrolling_engine')) &&
            ($listBlockName = $engineBlock->getData('list_block_name')) &&
            ($listBlock = $layout->getBlock($listBlockName)) &&
            ($toolbarBlock = $listBlock->getToolbarBlock())
        ) {
            // for toolbar to set page limit successfully, the limit must be added to available
            // limits
            $toolbarBlock->unsetData('_current_limit');
            $toolbarBlock->addPagerLimit($toolbarBlock->getCurrentMode(), $this->_limit);

            $html = $layoutHelper->renderBlock($listBlockName);
        }

        Mage::app()->getResponse()->setBody($html);
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