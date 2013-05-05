<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterSeoLinks
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/* BASED ON SNIPPET: New Module/Helper/Data.php */
class ManaPro_FilterSeoLinks_Helper_ParameterHandler_Toolbar extends Mana_Seo_Helper_ParameterHandler {
    /**
     * @param Mana_Seo_Model_Context $context
     * @param object[] $activeVariations
     * @param object[] $obsoleteVariations
     * @throws Exception
     * @return Mana_Seo_Helper_VariationSource
     */
    public function getVariations($context, &$activeVariations, &$obsoleteVariations) {
        $activeVariations = array();
        $obsoleteVariations = array();


        /* @var $helper ManaPro_FilterSeoLinks_Helper_Data */
        $helper = Mage::helper('manapro_filterseolinks');
        $vars = $context->getSchema()->getJson('toolbar_url_keys');

        foreach ($context->getCandidates() as $candidate) {
            foreach ($vars as $var) {
                if ($var['name'] == $candidate) {
                    /* @var $parameter Mana_Seo_Model_Parameter */
                    $parameter = Mage::getModel('mana_seo/parameter');
                    $parameter
                        ->setName($var['name'])
                        ->setInternalName($var['internal_name'])
                        ->setNextPoints(array('single_value'));

                    $activeVariations[] = $parameter;
                }
            }
        }
    }
}