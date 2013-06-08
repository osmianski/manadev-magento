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
    protected $_pageTypes;
    protected $_parameterSchemaProviders;
    protected $_urlTypes;
    protected $_parameterHandlers;
    protected $_variationPoints;

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

    /**
     * @param array | bool $types
     * @return Mana_Seo_Helper_Url[]
     */
    public function getUrlTypes($types = false) {
        if ($types === false) {
            if (!$this->_urlTypes) {
                /* @var $core Mana_Core_Helper_Data */
                $core = Mage::helper('mana_core');

                $result = array();

                foreach ($core->getSortedXmlChildren(Mage::getConfig()->getNode('mana_seo'), 'url_types') as $urlTypeXml) {
                    $result[(string)$urlTypeXml->helper] = Mage::helper((string)$urlTypeXml->helper);
                }
                $this->_urlTypes = $result;
            }

            return $this->_urlTypes;
        }
        else {
            $result = array();
            foreach ($this->getUrlTypes() as $type => $helper) {
                if (in_array('page', $types) && $helper->isPage() ||
                    in_array('parameter', $types) && $helper->isParameter() ||
                    in_array('value', $types) && $helper->isValue())
                {
                    $result[] = $type;
                }
            }

            return $result;
        }
    }

    /**
     * @return Mana_Seo_Helper_PageType[]
     */
    public function getPageTypes() {
        if (!$this->_pageTypes) {
            /* @var $core Mana_Core_Helper_Data */
            $core = Mage::helper('mana_core');

            $result = array();

            foreach ($core->getSortedXmlChildren(Mage::getConfig()->getNode('mana_seo'), 'page_types') as $key => $pageTypeXml) {
                $result[$key] = Mage::helper((string)$pageTypeXml->helper);
            }
            $this->_pageTypes = $result;
        }

        return $this->_pageTypes;
    }

    public function getPageType($type) {
        $pageTypes = $this->getPageTypes();
        return $pageTypes[$type];
    }

    public function isManadevLayeredNavigationInstalled() {
        return $this->isModuleEnabled('ManaPro_FilterSeoLinks');
    }

    public function isManadevAttributePageInstalled() {
        return $this->isModuleEnabled('Mana_AttributePage');
    }
}