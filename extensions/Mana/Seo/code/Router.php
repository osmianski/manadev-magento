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
            ->setMode(Mana_Seo_Model_Context::MODE_DIAGNOSTIC)
            ->setAction(Mana_Seo_Model_Context::ACTION_FORWARD)
            ->setRequest($request)
            ->setPath($path)
            ->setOriginalSlash($origSlash)
            ->setAlternativeSlash($altSlash);

        $context->setStoreId(Mage::app()->getStore()->getId());

        $this->_matches = array();
        $this->_matchVariationPoint($context, $seo->getFirstVariationPoint());
        if (count($this->_matches)) {
            $matches = $this->_matches;
            $this->_matches = array();
            if ($context->getMode() == Mana_Seo_Model_Context::MODE_DIAGNOSTIC) {
                $this->_logMatches($matches);
            }
            $this->_processMatch($matches[0]);
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_VariationPoint $variationPoint
     * @return bool
     */
    protected function _matchVariationPoint($context, $variationPoint) {
        $allObsoleteVariations = array();
        $variationPointHelper = $variationPoint->getHelper();
        $action = $context->getAction();

        $variationPointHelper->registerPoint($context, $variationPoint);
        foreach ($variationPointHelper->getVariationSources($variationPoint) as $variationSource) {
            $variationSource->getVariations($context, $activeVariations, $obsoleteVariations);
            foreach ($activeVariations as $variation) {
                $variationPointHelper->registerVariation($context, $variationPoint, $variation);
                if ($nextVariationPoints = $variationPointHelper->getNextVariationPoints($context, $variationPoint, $variation)) {
                    foreach ($nextVariationPoints as $nextVariationPoint) {
                        if ($this->_matchVariationPoint($context, $nextVariationPoint)) {
                            return true;
                        }
                    }
                }
                else {
                    if ($this->_registerMatch($context)) {
                        return true;
                    }
                }
                $variationPointHelper->unregisterVariation($context, $variationPoint,  $variation);
            }
            $allObsoleteVariations = array_merge($allObsoleteVariations, $obsoleteVariations);
        }
        $context->setAction(Mana_Seo_Model_Context::ACTION_REDIRECT);
        foreach ($allObsoleteVariations as $variation) {
            $variationPointHelper->registerVariation($context, $variationPoint, $variation);
            if ($nextVariationPoints = $variationPointHelper->getNextVariationPoints($context, $variationPoint, $variation)) {
                foreach ($nextVariationPoints as $nextVariationPoint) {
                    if ($this->_matchVariationPoint($context, $nextVariationPoint)) {
                        return true;
                    }
                }
            }
            else {
                if ($this->_registerMatch($context)) {
                    return true;
                }
            }
            $variationPointHelper->unregisterVariation($context, $variationPoint, $variation);
        }
        $context->setAction($action);
        $variationPointHelper->unregisterPoint($context, $variationPoint);

        return false;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @return bool
     */
    protected function _registerMatch($context) {
        $this->_matches[] = clone $context;
        return ($context->getMode() == Mana_Seo_Model_Context::MODE_OPTIMIZED);
    }

    /**
     * @param Mana_Seo_Model_Context[] $matches
     * @return Mana_Seo_Router
     */
    protected function _logMatches($matches) {
        foreach ($matches as $context) {

        }
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @throws Exception
     * @return Mana_Seo_Router
     */
    protected function _processMatch($context) {
        switch ($context->getAction()) {
            case Mana_Seo_Model_Context::ACTION_FORWARD:
                break;
            case Mana_Seo_Model_Context::ACTION_REDIRECT:
                break;
            default:
                throw new Exception('Not implemented');
        }
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_ParameterSet $currentParameterSet
     * @return bool
     */
    protected function _matchPage($context, $currentParameterSet = null) {
        $page = $context->getPage();
        if (!$currentParameterSet) {
            /* @var $currentParameterSet Mana_Seo_Model_ParameterSet */
            $currentParameterSet = Mage::getModel('mana_seo/parameterSet');
            $currentParameterSet
                ->setQuery($page->getQuery())
                ->setExpect(Mana_Seo_Model_ParameterSet::EXPECT_PARAMETER);
        }

        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');

        /* @var $mbstring Mana_Core_Helper_Mbstring */
        $mbstring = Mage::helper('mana_core/mbstring');

        $paramSep = $context->getSchema()->getParamSeparator();
        $firstValueSep = $context->getSchema()->getFirstValueSeparator();
        $multiValueSep = $context->getSchema()->getMultipleValueSeparator();

        if ($query = $currentParameterSet->getQuery()) {
            $path = explode($firstValueSep, $query);
            $candidates = array();
            foreach (array_keys($path) as $index) {
                $candidates[] = implode($firstValueSep, array_slice($path, 0, $index + 1));
            }
            $currentParameterSet->setCandidates($candidates);

            $allObsoleteParameterSets = array();
            foreach ($seo->getParameterHandlers() as $parameterHandler) {
                $parameterHandler->findParameter($context, $currentParameterSet, $activeParameterSets, $obsoleteParameterSets);
                foreach ($activeParameterSets as $parameterSet) {
                    if ($this->_matchPage($context, $parameterSet)) {
                        return true;
                    }
                }
                $allObsoleteParameterSets = array_merge($allObsoleteParameterSets, $obsoleteParameterSets);
            }
            $context->setAction(Mana_Seo_Model_Context::ACTION_REDIRECT);
            foreach ($allObsoleteParameterSets as $parameterSet) {
                if ($this->_matchPage($context, $parameterSet)) {
                    return true;
                }
            }

            // do parameter matching
            // if doesn't match, do unique value matching
            // if doesn't match return false
            // if match found call self recursively with the rest ques=ry string
        }
        // do optimization checks
        // if fully optimized page is found return true
        // otherwise return false and add all found pages to obsolete pages array
        // write easy writable URL conflict log
        return true;
        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');

        /* @var $mbstring Mana_Core_Helper_Mbstring */
        $mbstring = Mage::helper('mana_core/mbstring');

        $page = $context->getPage();
        $paramSep = $context->getSchema()->getParamSeparator();
        $firstValueSep = $context->getSchema()->getFirstValueSeparator();
        $nextValueSep = $context->getSchema()->getMultipleValueSeparator();

        $query = $page->getQuery();
        $query = explode($firstValueSep, $query);
        $expect = 0; // both
    }

}