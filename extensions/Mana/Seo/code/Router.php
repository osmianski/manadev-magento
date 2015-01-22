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
class Mana_Seo_Router extends Mage_Core_Controller_Varien_Router_Abstract  {
    protected $_matches;
    protected $_lastMatch = false;

    public function match(Zend_Controller_Request_Http $request) {
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }

        if ($this->coreHelper()->getRoutePath() != '//') {
            return false;
        }

        /* @var $parser Mana_Seo_Helper_UrlParser */
        $parser = Mage::helper('mana_seo/urlParser');

        /* @var $urlModel Mana_Seo_Rewrite_Url */
        $urlModel = Mage::getModel('core/url');

        /* @var $routerHelper Mana_Core_Helper_Router */
        $routerHelper = Mage::helper('mana_core/router');

        /* @var $front Mage_Core_Controller_Varien_Front */
        $front = $this->getFront();

        $path = ltrim(urldecode(str_replace('+', '%2B', $request->getPathInfo())), '/');
        if ($parsedUrl = $parser->parse($path)) {
            $url = $urlModel->getUrl($parsedUrl->getRoute(), array_merge(
                array('_use_rewrite' => true, '_nosid' => true),
                $parsedUrl->getImplodedParameters(),
                count($parsedUrl->getQueryParameters())
                    ? array('_query' => $parsedUrl->getImplodedQueryParameters())
                    : array()));

            if ($parsedUrl->getStatus() == Mana_Seo_Model_ParsedUrl::STATUS_OK) {
                if (!$this->seoHelper()->getActiveSchema(Mage::app()->getStore()->getId())
                        ->getRedirectParameterOrder() ||
                    rawurldecode($urlModel->getRoutePath()) == $path)
                {
                    Mage::register('m_temporary_query_parameters', $parsedUrl->getQueryParameters());
                    Mage::register('m_parsed_url', $parsedUrl);
                    $routerHelper
                        ->changePath($parsedUrl->getPageUrlKey() . $parsedUrl->getSuffix())
                        ->forward($parsedUrl->getRoute(), $request,
                            array_merge($request->getUserParams(), $parsedUrl->getImplodedParameters()),
                            array_merge($_GET, $parsedUrl->getImplodedQueryParameters()));

                    // invoke CMS router before Standard Router
                    if (($cmsRouter = $front->getRouter('cms'))) {
                        return $cmsRouter->match($request);
                    }
                }
                else {
                    $front->getResponse()->setRedirect($url, 301);
                    $request->setDispatched(true);
                }
            }
            elseif ($parsedUrl->getStatus() == Mana_Seo_Model_ParsedUrl::STATUS_OBSOLETE) {
                $front->getResponse()->setRedirect($url, 301);
                $request->setDispatched(true);
            }
            elseif (Mage::getStoreConfig('mana/seo/max_correction_count')) {
                $front->getResponse()->setRedirect($url, 301);
                $request->setDispatched(true);
            }
        }

        return false;
    }

    #region Dependencies
    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    /**
     * @return Mana_Seo_Helper_Data
     */
    public function seoHelper() {
        return Mage::helper('mana_seo');
    }
    #endregion
}