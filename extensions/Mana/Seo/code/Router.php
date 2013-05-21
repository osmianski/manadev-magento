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
        $front = $observer->getEvent()->getFront();

        $front->addRouter('mana_seo', $this);
    }

    public function match(Zend_Controller_Request_Http $request) {
        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');

        /* @var $logger Mana_Core_Helper_Logger */
        $logger = Mage::helper('mana_core/logger');

        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }

        $origSlash = (substr($request->getPathInfo(), -1) == '/') ? '/' : '';
        $altSlash = $origSlash ? '' : '/';
        $path = trim($request->getPathInfo(), '/');

        /* @var $context Mana_Seo_Model_Context */
        $context = Mage::getModel('mana_seo/context');
        $context
            ->setRouter($this)
            ->setMode(Mana_Seo_Model_Context::MODE_DIAGNOSTIC)
            ->setAction(Mana_Seo_Model_Context::ACTION_FORWARD)
            ->setRequest($request)
            ->setPath($path)
            ->setOriginalSlash($origSlash)
            ->setAlternativeSlash($altSlash);

        $logger->beginSeo("Processing $path ...");
        $context->setStoreId(Mage::app()->getStore()->getId());

        $this->_matches = array();
        $seo->getSchemaVariationPoint()->match($context);
        if (count($this->_matches)) {
            $matches = $this->_matches;
            $this->_matches = array();
            if ($context->getMode() == Mana_Seo_Model_Context::MODE_DIAGNOSTIC) {
                $this->_logMatches($context, $matches);
            }
            $this->_processMatch($matches[0]);
            $logger->endSeo();

            return true;
        }
        else {
            $logger->endSeo();

            $this->_lastMatch = false;
            return false;
        }
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @return bool
     */
    public function registerMatch($context) {
        $this->_matches[] = clone $context;
        return ($context->getMode() == Mana_Seo_Model_Context::MODE_OPTIMIZED);
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_Context[] $matches
     * @return Mana_Seo_Router
     */
    protected function _logMatches($context, $matches) {
        /* @var $logger Mana_Core_Helper_Logger */
        $logger = Mage::helper('mana_core/logger');

        $logger->beginSeoMatch("Matches found for '{$context->getPath()}'");
        foreach ($matches as $index => $match) {
            $logger->beginSeoMatch("Match $index:");
            $logger->logSeoMatch("Action: '{$match->getAction()}'");
            $logger->logSeoMatch("Schema: '{$match->getSchema()->getName()}'");
            $logger->logSeoMatch("Page: '{$match->getPageUrl()->getUrlKey()}' (type: {$match->getPageUrl()->getType()})");
            if (($parameters = $match->getParameters()) && count($parameters)) {
                $logger->beginSeoMatch("Parameters");
                foreach ($parameters as $parameter => $values) {
                    $logger->logSeoMatch("$parameter: ".implode(', ', $values));
                }
                $logger->endSeoMatch();
            }
            $logger->logSeoMatch(("Suffix: '{$match->getSuffix()}'"));
            $logger->endSeoMatch();
        }
        $logger->endSeoMatch();
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @throws Exception
     * @return Mana_Seo_Router
     */
    protected function _processMatch($context) {
        $params = array();
        if (($parameters = $context->getParameters()) && count($parameters)) {
            foreach ($parameters as $parameter => $values) {
                $params[$parameter] = implode('_', $values);
            }
        }

        $request = $context->getRequest();

        switch ($context->getAction()) {
            case Mana_Seo_Model_Context::ACTION_FORWARD:
                $route = explode('/', $context->getPageUrl()->getHelper()->getRoute($context, $params));

                /* @noinspection PhpUndefinedMethodInspection */
                $request->initForward();

                if (count($params)) {
                    $request->setParams($params);
                }
                $request
                    ->setModuleName($route[0])
                    ->setControllerName($route[1])
                    ->setActionName($route[2])
                    ->setDispatched(false);

                $this->_lastMatch = $context;
                break;
            case Mana_Seo_Model_Context::ACTION_REDIRECT:
                /* @var $front Mage_Core_Controller_Varien_Front */
                $front = $this->getFront();

                $url = Mage::getUrl('', array(
                    '_direct' => $context->getPageUrl()->getHelper()->getDirectUrl($context),
                    '_current' => true,
                    '_m_escape' => '',
                    '_use_rewrite' => true,
                    '_query' => $params,
                    '_nosid' => defined('_TEST'),
                    '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure())
                );
                $relativeUrl = substr($url, strlen(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK,
                    Mage::app()->getFrontController()->getRequest()->isSecure())));
                if (!defined('_TEST')) {
                    $front->getResponse()->setRedirect($url);
                }
                $this->_lastMatch = $relativeUrl;
                $request->setDispatched(true);

                break;
            default:
                throw new Exception('Not implemented');
        }
    }

    /**
     * @param Mana_Seo_Model_Context $context
     */
    protected function _forward($context) {
    }

    /**
     * @return Mana_Seo_Model_Context | string | bool
     */
    public function getLastMatch() {
        return $this->_lastMatch;
    }
}