<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterSeoLinks
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/* BASED ON SNIPPET: New Module/Helper/Data.php */
class ManaPro_FilterSeoLinks_Helper_ParameterHandler_Filters extends Mana_Seo_Helper_ParameterHandler {
    /**
     * @param Mana_Seo_Model_Context $context
     * @param object[] $activeVariations
     * @param object[] $obsoleteVariations
     * @throws Exception
     * @return Mana_Seo_Interface_VariationSource
     */
    public function getVariations($context, &$activeVariations, &$obsoleteVariations) {
        $activeVariations = array();
        $obsoleteVariations = array();

        /* @var $helper Mana_Filters_Helper_Data */
        $helper = Mage::helper(strtolower('Mana_Filters'));
        $collection = $helper->getFilterOptionsCollection(true);

        foreach ($context->getCandidates() as $candidate) {
            $candidateNames = array($seoName, Mage::getStoreConfigFlag('mana_filters/seo/use_label') ? $_core->urlToLabel($seoName) : $_core->lowerCased($seoName));
            foreach ($collection as $item) {
                /* @var $item Mana_Filters_Model_Filter2_Store */
                if (Mage::getStoreConfigFlag('mana_filters/seo/use_label')) {
                    if (in_array($item->getLowerCaseName(), $candidates)) {
                        return $item->getCode();
                    }
                }
                else {
                    if (in_array(strtolower($item->getCode()), $candidates)) {
                        return $item->getCode();
                    }
                }
            }
        }
    }
}