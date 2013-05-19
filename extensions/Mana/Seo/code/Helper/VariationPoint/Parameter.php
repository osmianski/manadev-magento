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
class Mana_Seo_Helper_VariationPoint_Parameter extends Mana_Seo_Helper_VariationPoint {
    /**
     * @param Mana_Seo_Model_Context $context
     * @return Mana_Seo_Helper_VariationPoint_Parameter
     */
    protected function _before($context) {
        $context->pushData('candidates', $this->_parsePath($context->getPath(),
            array_unique(array(
                $context->getSchema()->getFirstValueSeparator(),
                $context->getSchema()->getMultipleValueSeparator(),
                $context->getSchema()->getParamSeparator(),
            ))));

        return $this;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_Url $parameterUrl
     * @return Mana_Seo_Helper_VariationPoint_Parameter
     */
    protected function _register($context, $parameterUrl) {
        $parameterUrl->getHelper()->registerParameter($context, $parameterUrl);

        /* @var $mbstring Mana_Core_Helper_Mbstring */
        $mbstring = Mage::helper('mana_core/mbstring');

        $path = $context->getPath();
        $path = $mbstring->substr($path, $mbstring->strlen($parameterUrl->getUrlKey()));
        if ($path) {
            throw new Exception('Not implemented');
        }
        if ($parameterUrl->getIsValue()) {

        }
        else {
        }
        $context->pushData('path', $path);

        return $this;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_Url $parameterUrl
     * @return Mana_Seo_Helper_VariationPoint_Parameter
     */
    protected function _unregister($context, $parameterUrl) {
        $parameterUrl->getHelper()->unregisterParameter($context, $parameterUrl);
        $context->popData('path');

        return $this;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @return Mana_Seo_Helper_VariationPoint_Parameter
     */
    protected function _after($context) {
        $context->popData('candidates');

        return $this;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @return bool
     */
    public function match($context) {
         if (!$context->getPath()) {
            return $context->getRouter()->registerMatch($context);
        }

        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');

        $allObsoleteParameterUrls = array();
        $action = $context->getAction();

        $this->_before($context);

        foreach ($seo->getParameterHandlers() as $parameterHandler) {
            $parameterHandler->getParameterUrls($context, $activeParameterUrls, $obsoleteParameterUrls);
            foreach ($activeParameterUrls as $parameterUrl) {
                /* @var $parameterUrl Mana_Seo_Model_Url */
                $this->_register($context, $parameterUrl);

                if ($seo->getParameterVariationPoint()->match($context)) {
                    return true;
                }

                $this->_unregister($context, $parameterUrl);
            }
            $allObsoleteParameterUrls = array_merge($allObsoleteParameterUrls, $obsoleteParameterUrls);
        }

        $context->setAction(Mana_Seo_Model_Context::ACTION_REDIRECT);
        foreach ($allObsoleteParameterUrls as $parameterUrl) {
            $this->_register($context, $parameterUrl);

            if ($seo->getParameterVariationPoint()->match($context)) {
                return true;
            }

            $this->_unregister($context, $parameterUrl);
        }

        $context->setAction($action);
        $this->_after($context);

        return false;
    }
}