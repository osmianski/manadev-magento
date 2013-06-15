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

    /**
     * Initialize Controller Router
     *
     * @param Varien_Event_Observer $observer
     */
    public function initControllerRouters($observer) {
        /* @var $front Mage_Core_Controller_Varien_Front */
        $front = $observer->getEvent()->getData('front');

        $front->addRouter('mana_seo', $this);
    }

    public function match(Zend_Controller_Request_Http $request) {
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }

        /* @var $parser Mana_Seo_Helper_UrlParser */
        $parser = Mage::helper('mana_seo/urlParser');

        /* @var $urlModel Mana_Seo_Rewrite_Url */
        $urlModel = Mage::getModel('core/url');

        $path = ltrim($request->getPathInfo(), '/');
        if ($parsedUrl = $parser->parse($path)) {
            $url = $urlModel->getUrl($parsedUrl->getRoute(), array_merge(
                array('_use_rewrite' => true, '_nosid' => true),
                $parsedUrl->getImplodedParameters(),
                count($parsedUrl->getQueryParameters())
                    ? array('_query' => $parsedUrl->getImplodedQueryParameters())
                    : array()));

            if ($parsedUrl->getStatus() == Mana_Seo_Model_ParsedUrl::STATUS_OK &&
                $urlModel->getRoutePath() == $path)
            {
                $route = explode('/', $parsedUrl->getRoute());

                /* @noinspection PhpUndefinedMethodInspection */
                $request->initForward();

                $request
                    ->setParams(array_merge($request->getParams(),
                        $parsedUrl->getImplodedParameters()))
                    ->setModuleName($route[0])
                    ->setControllerName($route[1])
                    ->setActionName($route[2])
                    ->setDispatched(false);

                $_GET = array_merge($_GET, $parsedUrl->getImplodedQueryParameters());
            }
            else {
                /* @var $front Mage_Core_Controller_Varien_Front */
                $front = $this->getFront();

                $front->getResponse()->setRedirect($url);
                $request->setDispatched(true);
            }

            return true;
        }
        else {
            return false;
        }
    }
}