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
class Mana_Core_Helper_PageType_Category extends Mana_Core_Helper_PageType  {
    public function getCurrentSuffix() {
        /* @var $categoryHelper Mage_Catalog_Helper_Category */
        $categoryHelper = Mage::helper('catalog/category');

        return $categoryHelper->getCategoryUrlSuffix();
    }


    public function getRoutePath() {
        return 'catalog/category/view';
    }

    public function isProductListVisible() {
        if ($category = Mage::registry('current_category')) {
            return $category->getData('display_mode') != 'PAGE';
        }
        return true;
    }


    /**
     * @return bool|string
     */
    public function getConditionLabel() {
        return $this->__('Category Page');
    }

    public function getPageContent() {
        if ($category = Mage::registry('current_category')) {
            $result = array(
                'title' => $category->getName(),
                'description' => $category->getData('description'),
            );
            if ($title = $category->getMetaTitle()) {
                $result['meta_title'] = $title;
            }
            if ($description = $category->getMetaDescription()) {
                $result['meta_description'] = $description;
            }
            if ($keywords = $category->getMetaKeywords()) {
                $result['meta_keywords'] = $keywords;
            }
            return array_merge(parent::getPageContent(), $result);
        }
        return parent::getPageContent();
    }
}