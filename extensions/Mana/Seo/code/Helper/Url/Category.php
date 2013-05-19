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
class Mana_Seo_Helper_Url_Category extends Mana_Seo_Helper_Url {
    /**
     * @param Mana_Seo_Model_Context $context
     * @return bool|Mana_Seo_Helper_VariationPoint_Suffix
     */
    public function getSuffixVariationPoint(/** @noinspection PhpUnusedParameterInspection */$context) {
        return Mage::helper('mana_seo/variationPoint_suffix_category');
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @param array $params
     * @return string
     */
    public function getRoute($context, &$params) {
        $params['id'] = $context->getPageUrl()->getCategoryId();
        return 'catalog/category/view';
    }
}