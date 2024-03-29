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
class Mana_AttributePage_Helper_PageType_OptionPage extends Mana_Core_Helper_PageType  {
    public function getCurrentSuffix() {
        return Mage::getStoreConfig('mana_attributepage/seo/option_page_url_suffix', Mage::app()->getStore());
    }


    public function getRoutePath() {
        return 'mana/optionPage/view';
    }

    public function getSuffixHistoryType() {
        return Mana_Seo_Model_UrlHistory::TYPE_OPTION_PAGE_SUFFIX;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    public function setPage($token) {
        $token
            ->setRoute($this->getRoutePath())
            ->setIsRedirectToSubcategoryPossible(false)
            ->addParameter('id', $token->getPageUrl()->getData('option_page_id'));

        return true;
    }

    protected $_urlKeys = array();

    /**
     * @param Mana_Seo_Rewrite_Url $urlModel
     * @return string | bool
     */
    public function getUrlKey($urlModel) {
        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');

        /* @var $logger Mana_Core_Helper_Logger */
        $logger = Mage::helper('mana_core/logger');

        if (($optionPageId = $urlModel->getSeoRouteParam('id')) === false) {
            $logger->logSeoUrl(sprintf('WARNING: while resolving %s, %s route parameter is required', 'option page URL key', 'id'));
        }
        if (!isset($this->_urlKeys[$optionPageId])) {
            $urlCollection = $seo->getUrlCollection($urlModel->getSchema(), Mana_Seo_Resource_Url_Collection::TYPE_PAGE);

            $loadId = $optionPageId;
            if(isset($_GET['___from_store'])) {
                $fromStore = Mage::app()->getStore($_GET['___from_store']);
                $fromStoreModel = Mage::getModel('mana_attributepage/optionPage_store')->setData('store_id', $fromStore->getId())->load($optionPageId);
                $newStoreModel = Mage::getModel('mana_attributepage/optionPage_store')->setData('store_id', $urlModel->getStore()->getId())
                    ->load($fromStoreModel->getData('option_page_global_id'), 'option_page_global_id');
                $loadId = $newStoreModel->getId();
            }

            $urlCollection->addFieldToFilter('option_page_id', $loadId);
            if (!($result = $urlModel->getUrlKey($urlCollection))) {
                $logger->logSeoUrl(sprintf('WARNING: %s not found by  %s %s', 'option page URL key', 'id', $optionPageId));
            }

            $this->_urlKeys[$optionPageId] = $result;
        }

        return $this->_urlKeys[$optionPageId]['final_url_key'];
    }

    /**
     * @return bool|string
     */
    public function getConditionLabel() {
        return $this->__('Attribute Option Page');
    }

    public function getPageContent() {
        /* @var $optionPage Mana_AttributePage_Model_OptionPage_Store */
        $optionPage = Mage::registry('current_option_page');

        $result = array(
            'title' => $optionPage->getData('heading'),
            'description' => $optionPage->getData('description'),
        );
        if ($title = $optionPage->getData('meta_title')) {
            $result['meta_title'] = $title;
        }
        if ($description = $optionPage->getData('meta_description')) {
            $result['meta_description'] = $description;
        }
        if ($keywords = $optionPage->getData('meta_keywords')) {
            $result['meta_keywords'] = $keywords;
        }
        return array_merge(parent::getPageContent(), $result);
    }

    public function getPageTypeId() {
        /* @var $optionPage Mana_AttributePage_Model_OptionPage_Store */
        $optionPage = Mage::registry('current_option_page');

        return 'option:' . $optionPage->getData('option_page_global_id');
    }
}
