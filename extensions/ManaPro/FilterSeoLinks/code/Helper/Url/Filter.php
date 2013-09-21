<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterSeoLinks
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterSeoLinks_Helper_Url_Filter extends Mana_Seo_Helper_Url_Composite_Parameter {
    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_Url $parameterUrl
     * @return Mana_Seo_Helper_Url
     */
    public function registerParameter($context, $parameterUrl) {
        $context
            ->pushData('expect_value', 1)
            ->pushData('current_parameter', $parameterUrl->getInternalParameterName());
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_Url $parameterUrl
     * @return Mana_Seo_Helper_Url
     */
    public function unregisterParameter($context, $parameterUrl) {
        $context->popData('expect_value');
        $context->popData('current_parameter');
    }
}