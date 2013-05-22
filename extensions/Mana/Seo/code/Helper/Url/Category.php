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
    protected $_type = 'category';

    public function isPage() {
        return true;
    }

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

    /**
     * @param Mana_Seo_Model_Context $context
     * @return string
     */
    public function getDirectUrl(/** @noinspection PhpUnusedParameterInspection */$context) {
        /* @var $category Mage_Catalog_Model_Category */
        $category = Mage::getModel('catalog/category');
        $category
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($context->getPageUrl()->getCategoryId());
        $url = $category->getUrl();
        return substr($url, strlen(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK,
            Mage::app()->getFrontController()->getRequest()->isSecure())));
    }

    /**
     * @param string $route
     * @return bool
     */
    public function recognizeRoute($route) {
        return $route == 'catalog/category/view';
    }
}