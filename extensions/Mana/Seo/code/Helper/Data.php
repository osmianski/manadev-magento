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
     * @return Mana_Seo_Helper_PageType[]
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

    public function getFirstVariationPoint() {
        if (!$this->_variationPoints) {
            /* @var $core Mana_Core_Helper_Data */
            $core = Mage::helper('mana_core');

            $result = array();

            /* @var $previousVariationPoint Mana_Seo_Model_VariationPoint */
            $previousVariationPoint = null;

            foreach ($core->getSortedXmlChildren(Mage::getConfig()->getNode('mana_seo'), 'variation_points') as $xml) {
                /* @var $variationPoint Mana_Seo_Model_VariationPoint */
                $variationPoint = Mage::getModel('mana_seo/variationPoint');
                /** @noinspection PhpUndefinedMethodInspection */
                $variationPoint
                    ->setHelper(Mage::helper((string)$xml->helper))
                    ->setXml($xml)
                    ->setName($xml->getName());

                if ($previousVariationPoint) {
                    $previousVariationPoint->setNextPoint($variationPoint);
                }
                $result[] = $previousVariationPoint = $variationPoint;
            }
            $this->_variationPoints = $result;
        }

        return $this->_variationPoints[0];
    }
}