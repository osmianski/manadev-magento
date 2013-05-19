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
class Mana_Seo_Helper_Url extends Mage_Core_Helper_Abstract {
    /**
     * @param Mana_Seo_Model_Context $context
     * @return bool|Mana_Seo_Helper_VariationPoint_Suffix
     */
    public function getSuffixVariationPoint(/** @noinspection PhpUnusedParameterInspection */$context) {
        return false;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_Url $parameterUrl
     * @return Mana_Seo_Helper_Url
     */
    public function registerParameter($context, $parameterUrl) {
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_Url $parameterUrl
     * @return Mana_Seo_Helper_Url
     */
    public function unregisterParameter($context, $parameterUrl) {
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param array $params
     * @throws Exception
     * @return string
     */
    public function getRoute(/** @noinspection PhpUnusedParameterInspection */$context, &$params) {
        throw new Exception('Not implemented');
    }
}