<?php
/** 
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Content_Helper_PageType_Book extends Mana_Core_Helper_PageType {
    public function getCurrentSuffix() {
        return null;
    }

    public function getRoutePath() {
        return 'mana_content/book/view';
    }

    public function getSuffixHistoryType() {
        return Mana_Seo_Model_UrlHistory::TYPE_BOOK_PAGE_SUFFIX;
    }

    /**
     * @param Mana_Seo_Model_ParsedUrl $token
     * @return bool
     */
    public function setPage($token) {
        $token
            ->setRoute($this->getRoutePath())
            ->setIsRedirectToSubcategoryPossible(false)
            ->addParameter('id', $token->getPageUrl()->getData('book_page_id'));

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

        if (($bookPageId = $urlModel->getSeoRouteParam('id')) === false) {
            $logger->logSeoUrl(sprintf('WARNING: while resolving %s, %s route parameter is required', 'attribute page URL key', 'id'));
        }
        if (!isset($this->_urlKeys[$bookPageId])) {
            $urlCollection = $seo->getUrlCollection($urlModel->getSchema(), Mana_Seo_Resource_Url_Collection::TYPE_PAGE);
            $urlCollection->addFieldToFilter('book_page_id', $bookPageId);
            if (!($result = $urlModel->getUrlKey($urlCollection))) {
                $logger->logSeoUrl(sprintf('WARNING: %s not found by  %s %s', 'book page URL key', 'id', $bookPageId));
            }

            $this->_urlKeys[$bookPageId] = $result;
        }

        return $this->_urlKeys[$bookPageId]['final_url_key'];
    }

    public function getPageContent() {
        $bookPage = $this->getCurrentBook();

        $result = array(
            'title' => $bookPage->getData('title'),
            'description' => $bookPage->getData('content'),
        );
        if ($title = $bookPage->getData('meta_title')) {
            $result['meta_title'] = $title;
        }
        if ($description = $bookPage->getData('meta_description')) {
            $result['meta_description'] = $description;
        }
        if ($keywords = $bookPage->getData('meta_keywords')) {
            $result['meta_keywords'] = $keywords;
        }

        return array_merge(parent::getPageContent(), $result);
    }

    public function getPageTypeId() {
        return 'book:' . $this->getCurrentBook()->getData('page_global_id');
    }

    #region Dependencies

    /**
     * @return Mana_Content_Model_Page_Store
     */
    protected function getCurrentBook() {
        return Mage::registry('current_book_page');
    }
    #endregion

}