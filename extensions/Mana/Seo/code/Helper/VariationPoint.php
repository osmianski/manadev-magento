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
abstract class Mana_Seo_Helper_VariationPoint extends Mage_Core_Helper_Abstract {
    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_VariationPoint $variationPoint
     * @param object $variation
     * @return Mana_Seo_Model_VariationPoint[] | bool
     */
    public function getNextVariationPoints(/** @noinspection PhpUnusedParameterInspection */$context, $variationPoint, $variation) {
        return $variationPoint->getNextPoint() ? array($variationPoint->getNextPoint()) : false;
    }

    /**
     * @param Mana_Seo_Model_VariationPoint $variationPoint
     * @return Mana_Seo_Helper_VariationSource[]
     */
    abstract public function getVariationSources($variationPoint);

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_VariationPoint $variationPoint
     * @param object $variation
     * @return Mana_Seo_Helper_VariationPoint
     */
    abstract public function registerVariation($context, $variationPoint, $variation);

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_VariationPoint $variationPoint
     * @param object $variation
     * @return Mana_Seo_Helper_VariationPoint
     */
    abstract public function unregisterVariation($context, $variationPoint, $variation);

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_VariationPoint $variationPoint
     * @return Mana_Seo_Helper_VariationPoint
     */
    public function registerPoint(/** @noinspection PhpUnusedParameterInspection */$context, $variationPoint) {
        return $this;
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_VariationPoint $variationPoint
     * @return Mana_Seo_Helper_VariationPoint
     */
    public function unregisterPoint(/** @noinspection PhpUnusedParameterInspection */$context, $variationPoint) {
        return $this;
    }

    /**
     * @param string $haystack
     * @param string $sep
     * @param bool $throwIfSepEmpty
     * @throws Exception
     * @return string[]
     */
    protected function _parsePath($haystack, $sep, $throwIfSepEmpty = true) {
        if ($sep) {
            $path = explode($sep, $haystack);
            $candidates = array();
            foreach (array_keys($path) as $index) {
                $candidates[] = implode($sep, array_slice($path, 0, $index + 1));
            }
            return $candidates;
        }
        else {
            if ($throwIfSepEmpty) {
                throw new Exception(Mage::helper('mana_seo')->__("Path separator can't be empty"));
            }
            else {
                return array($haystack);
            }
        }
    }
}