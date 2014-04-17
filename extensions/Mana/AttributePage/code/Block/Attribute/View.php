<?php
/** 
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_AttributePage_Block_Attribute_View extends Mage_Core_Block_Template {
    protected function _prepareLayout() {
        parent::_prepareLayout();

        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            /* @var $breadcrumbsBlock Mage_Page_Block_Html_Breadcrumbs */
            $breadcrumbsBlock->addCrumb('home', array(
                    'label' => $this->__('Home'),
                    'title' => $this->__('Go to Home Page'),
                    'link' => Mage::getBaseUrl()
            ));
            $breadcrumbsBlock->addCrumb('m-attribute-page', array(
                    'label' => $this->getAttributePage()->getData('title'),
                    'title' => $this->getAttributePage()->getData('title'),
                    'last' => true,
                ));
        }
        $title = $this->getAttributePage()->getData('meta_title');

        if ($headBlock = $this->getLayout()->getBlock('head')) {
            /* @var $headBlock Mage_Page_Block_Html_Head */
            $attributePage = $this->getAttributePage();
            if ($title) {
                $headBlock->setTitle($title);
            }
            if ($description = $attributePage->getData('meta_description')) {
                $headBlock->setData('description', $description);
            }
            if ($keywords = $attributePage->getData('meta_keywords')) {
                $headBlock->setData('keywords', $keywords);
            }
        }

        return $this;
    }

    /**
     * Retrieve HTML title value separator (with space)
     *
     * @param mixed $store
     * @return string
     */
    public function getTitleSeparator($store = null) {
        $separator = (string)Mage::getStoreConfig('catalog/seo/title_separator', $store);

        return ' ' . $separator . ' ';
    }

    #region Dependencies
    /**
     * @return Mana_AttributePage_Model_AttributePage_Store
     */
    public function getAttributePage() {
        return Mage::registry('current_attribute_page');
    }

    /**
     * @return Mana_Core_Helper_Files
     */
    public function filesHelper() {
        return Mage::helper('mana_core/files');
    }
    #endregion
}