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
    protected $_type = '';
    protected $_xml = null;

    public function getXml() {
        if (is_null($this->_xml)) {
            $result = Mage::getConfig()->getXpath("//mana_seo/url_types/{$this->_type}");

            $this->_xml = count($result) == 1 ? $result[0] : false;
        }

        return $this->_xml;
    }

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

    /**
     * @param Mana_Seo_Model_Context $context
     * @throws Exception
     * @return string
     */
    public function getDirectUrl(/** @noinspection PhpUnusedParameterInspection */$context) {
        throw new Exception('Not implemented');
    }

    /**
     * @param string $route
     * @return bool
     */
    public function recognizeRoute(/** @noinspection PhpUnusedParameterInspection */$route) {
        return false;
    }

    public function getSuffix() {
        if ($suffixHelper = $this->getSuffixVariationPoint(null)) {
            return $suffixHelper->getCurrentSuffix();
        }
        else {
            return '';
        }
    }

    public function isPage() {
        return false;
    }

    public function isParameter() {
        return false;
    }

    public function isValue() {
        return false;
    }
}