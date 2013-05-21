<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for Mana_Seo module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_Seo_Helper_Data extends Mage_Core_Helper_Abstract {
    protected $_parameterSchemaProviders;
    protected $_pageTypes;
    protected $_parameterHandlers;
    protected $_variationPoints;

    /**
     * @return Mana_Seo_Helper_Url[]
     */
    public function getPageTypes() {
        if (!$this->_pageTypes) {
            /* @var $core Mana_Core_Helper_Data */
            $core = Mage::helper('mana_core');

            $result = array();

            foreach ($core->getSortedXmlChildren(Mage::getConfig()->getNode('mana_seo'), 'page_types') as $pageTypeXml) {
                $result[] = Mage::helper((string)$pageTypeXml->helper);
            }
            $this->_pageTypes = $result;
        }
        return $this->_pageTypes;
    }

    /**
     * @return Mana_Seo_Helper_ParameterHandler[]
     */
    public function getParameterHandlers() {
        if (!$this->_parameterHandlers) {
            /* @var $core Mana_Core_Helper_Data */
            $core = Mage::helper('mana_core');

            $result = array();

            foreach ($core->getSortedXmlChildren(Mage::getConfig()->getNode('mana_seo'), 'parameter_handlers') as $parameterHandlerXml) {
                $result[] = Mage::helper((string)$parameterHandlerXml->helper);
            }
            $this->_parameterHandlers = $result;
        }
        return $this->_parameterHandlers;
    }

    /**
     * @return Mana_Seo_Helper_VariationPoint_Schema
     */
    public function getSchemaVariationPoint() {
        return Mage::helper('mana_seo/variationPoint_schema');
    }

    /**
     * @return Mana_Seo_Helper_VariationPoint
     */
    public function getPageUrlVariationPoint() {
        return Mage::helper('mana_seo/variationPoint_pageUrl');
    }

    /**
     * @return Mana_Seo_Helper_VariationPoint
     */
    public function getParameterVariationPoint() {
        return Mage::helper('mana_seo/variationPoint_parameter');
    }

    public function getParameterComparer($parameters) {
        /* @var $comparer Mana_Seo_Helper_ParameterComparer */
        $comparer = Mage::helper('mana_seo/parameterComparer');

        $positions = array();
        foreach ($this->getParameterHandlers() as $parameterHandler) {
            $positions = array_merge($positions, $parameterHandler->getParameterPositions($parameters));
        }

        $comparer->setPositions($positions);

        return $comparer;
    }
}