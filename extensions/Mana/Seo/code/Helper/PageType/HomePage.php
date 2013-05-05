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
class Mana_Seo_Helper_PageType_HomePage extends Mana_Seo_Helper_PageType  {
    /**
     * @param Mana_Seo_Model_Context $context
     * @param object[] $activeVariations
     * @param object[] $obsoleteVariations
     * @return Mana_Seo_Helper_VariationSource
     */
    public function getVariations($context, &$activeVariations, &$obsoleteVariations) {
        /* @var $page Mana_Seo_Model_Page */
        $page = Mage::getModel('mana_seo/page');
        $page
            ->setUrl('')
            ->setQuery($context->getPath());

        $activeVariations = array($page);
        $obsoleteVariations = array();
    }
}