<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Core_Helper_PageType_CmsPage extends Mana_Core_Helper_PageType  {
    public function getCurrentSuffix() {
        return Mage::getStoreConfig('mana/seo/cms_page_suffix');
    }

    public function getRoutePath() {
        return 'cms/page/view';
    }

    public function isProductListVisible() {
        if ($block = Mage::getSingleton('core/layout')->getBlock('cms.products')) {
            return ($block->hasData('hide_when_no_filters_applied')
                ? $block->getData('hide_when_no_filters_applied')
                : !Mage::getStoreConfigFlag('mana_filters/display/hide_cms_product_list'));
        }

        return true;
    }

    /**
     * @return bool|string
     */
    public function getConditionLabel() {
        return $this->__('CMS Page');
    }

    public function getPageContent() {
        $page = Mage::getSingleton('cms/page');

        $result = array(
            'meta_title' => $page->getData('title'),
            'meta_description' => $page->getData('meta_description'),
            'meta_keywords' => $page->getData('meta_keywords'),
            'title' => $page->getData('title'),
            'description' => $page->getData('content'),
        );
        return array_merge(parent::getPageContent(), $result);
    }


    public function getPageTypeId() {
        return 'cms:' . Mage::getSingleton('cms/page')->getId();
    }
}