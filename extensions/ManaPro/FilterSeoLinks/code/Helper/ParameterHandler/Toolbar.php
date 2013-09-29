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
     * @param Mana_Seo_Model_Url[] $activeParameterUrls
     * @param Mana_Seo_Model_Url[] $obsoleteParameterUrls
     * @return Mana_Seo_Helper_ParameterHandler
     */
    public function getParameterUrls($context, &$activeParameterUrls, &$obsoleteParameterUrls) {
        $activeParameterUrls = array();
        $obsoleteParameterUrls = array();

        $vars = $context->getSchema()->getJson('toolbar_url_keys');

        foreach ($context->getCandidates() as $candidate) {
            foreach ($vars as $var) {
                if ($var['name'] == $candidate) {
                    /* @var $parameterUrl Mana_Seo_Model_Url */
                    $parameterUrl = Mage::getModel('mana_seo/url');
                    $parameterUrl
                        ->setUrlKey($var['name']);

                    $activeParameterUrls[] = $parameterUrl;
                }
            }
        }
    }
}