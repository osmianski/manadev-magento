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
class Mana_Seo_Helper_VariationPoint_PageType extends Mana_Seo_Helper_VariationPoint
    implements Mana_Seo_Interface_VariationSource {
    /**
     * @param Mana_Seo_Model_VariationPoint $variationPoint
     * @return Mana_Seo_Interface_VariationSource[]
     */
    public function getVariationSources($variationPoint) {
        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');
        $result = $seo->getPageTypes();
        array_push($result, $this);
        return $result;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_VariationPoint $variationPoint
     * @return Mana_Seo_Helper_VariationPoint
     */
    public function registerPoint($context, $variationPoint) {
        $context->pushData('candidates', $this->_parsePath($context->getPath(), $context->getSchema()->getQuerySeparator(), false));

        return $this;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_VariationPoint $variationPoint
     * @return Mana_Seo_Helper_VariationPoint
     */
    public function unregisterPoint($context, $variationPoint) {
        $context->popData('candidates');

        return $this;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_VariationPoint $variationPoint
     * @param Mana_Seo_Model_Page $variation
     * @return Mana_Seo_Helper_VariationPoint
     */
    public function registerVariation($context, $variationPoint, $variation) {
        $context->setPage($variation);
        $context->pushData('query', $variation->getQuery());
        return $this;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_VariationPoint $variationPoint
     * @param Mana_Seo_Model_Page $variation
     * @return Mana_Seo_Helper_VariationPoint
     */
    public function unregisterVariation($context, $variationPoint, $variation) {
        $context->unsetData('page');
        $context->popData('query');

        return $this;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param object[] $activeVariations
     * @param object[] $obsoleteVariations
     * @return Mana_Seo_Interface_VariationSource
     */
    public function getVariations($context, &$activeVariations, &$obsoleteVariations) {
        $activeVariations = array();
        $obsoleteVariations = array();

        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        /* @var $collection Mana_Db_Resource_Entity_Collection */
        $collection = $dbHelper->getResourceModel('mana_seo/url_collection');
        $collection
            ->setStoreFilter($context->getStoreId())
            ->addFieldToFilter('url_key', array('in' => $context->getCandidates()))
            ->addFieldToFilter('is_page', 1)
            ->addFieldToFilter('status', array(
                'in' => array(
                    Mana_Seo_Model_Url::STATUS_ACTIVE,
                    Mana_Seo_Model_Url::STATUS_OBSOLETE
                )
            ));

        foreach ($collection as $pageUrl) {
            /* @var $pageUrl Mana_Seo_Model_Url */
            if ($pageUrl->getHelper()->isValidUrl($context, $pageUrl)) {
                if ($pageUrl->getStatus() == Mana_Seo_Model_Url::STATUS_ACTIVE) {
                    $activeVariations[] = $pageUrl;
                }
                else {
                    $obsoleteVariations[] = $pageUrl;
                }
            }
        }
    }
}