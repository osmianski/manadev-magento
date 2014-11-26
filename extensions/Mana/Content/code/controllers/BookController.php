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
class Mana_Content_BookController extends Mage_Core_Controller_Front_Action {

    public function viewAction() {

        if ($bookPage = $this->_initBookPage()) {
            $layoutXml = $this->_applyCustomDesign();

            $this->getLayout()->getUpdate()->addHandle('default');
            $this->addActionLayoutHandles();
            Mage::helper('mana_core/layout')->addRecursiveLayoutUpdates($layoutXml);

            $related_products = !is_null($this->getRequest()->getParam('related_products')) ? explode(",", $this->getRequest()->getParam('related_products')) : array();
            $tags = !is_null($this->getRequest()->getParam('tags')) ? explode(",", $this->getRequest()->getParam('tags')) : array();

            $filter = array(
                'search' => $this->getRequest()->getParam('search'),
                'related_products' => $related_products,
                'tags' => $tags,
            );
            Mage::register('filter', $filter);

            $this->loadLayoutUpdates();
            if (trim($layoutXml)) {
                $this->getLayout()->getUpdate()->addUpdate($layoutXml);
            }
            $this->generateLayoutXml();
            $this->generateLayoutBlocks();
            $this->_isLayoutLoaded = true;

            $head = $this->getLayout()->getBlock('head');
            if ($head) {
                $head->setTitle($bookPage->getTitle());
                $head->setKeywords($bookPage->getMetaKeywords());
                $head->setDescription($bookPage->getMetaDescription());
                if($canonicalUrl = Mage::getResourceModel('mana_content/page_globalCustomSettings')->getReferencePageUrl($bookPage->getId())) {
                    $params = array('_nosid' => true, '_current' => true, '_m_escape' => '', '_use_rewrite' => true);
                    $url = explode('?', Mage::getUrl('*/*/*', $params))[0];;
                    $head->removeItem('link_rel', $url);
                    $head->addLinkRel('canonical', $canonicalUrl);
                }
            }
            if ($pageLayout = $bookPage->getData('page_layout')) {
                $this->pageLayoutHelper()->applyTemplate($pageLayout);
            }
            // show breadcrumbs
            if (Mage::getStoreConfig('web/default/show_cms_breadcrumbs')
                && ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs'))
            ) {
                $breadcrumbs->addCrumb('home', array('label' => Mage::helper('cms')->__('Home'), 'title' => Mage::helper('cms')->__('Go to Home Page'), 'link' => Mage::getBaseUrl()));

                $crumbs = $bookPage->getParentPages();
                for($x = count($crumbs)-1; $x >= 0; $x--) {
                    $breadCrumb = $crumbs[$x]['title'];
                    $route = "mana_content/book/view";
                    $link = Mage::getUrl($route, array('_use_rewrite' => true, 'id' => $crumbs[$x]['id']));
                    $breadcrumbs->addCrumb($breadCrumb, array('label' => $breadCrumb, 'title' => $breadCrumb, 'link' => $link));
                }
            }


            /* @var $helper Mage_Cms_Helper_Data */
            $helper = Mage::helper('cms');
            $processor = $helper->getBlockTemplateProcessor();
            $bookPage->setContent($processor->filter($bookPage->getContent()));

            $this->renderLayout();
        } else {
            $this->_forward('noRoute');
        }
    }

    protected function _initBookPage() {
        Mage::dispatchEvent('mana_content_controller_book_page_init_before', array('controller_action' => $this));
        $bookPageId = (int)$this->getRequest()->getParam('id', false);
        if (!$bookPageId) {
            return false;
        }

        /* @var $bookPage Mana_Content_Model_Page_Store */
        $bookPage = Mage::getModel('mana_content/page_store');
        $bookPage->setData('store_id', Mage::app()->getStore()->getId());
        $bookPage->load($bookPageId);

        if (!$bookPage->canShow()) {
            return false;
        }
        Mage::register('current_book_page', $bookPage);
        try {
            Mage::dispatchEvent('mana_content_controller_book_page_init_after', array('book_page' => $bookPage, 'controller_action' => $this));
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);

            return false;
        }

        return $bookPage;
    }

    protected function _applyCustomDesign() {
        /* @var $page Mana_Content_Model_Page_Store */
        $page = Mage::registry('current_book_page');

        $result = $page->getData('layout_xml');
        if (Mage::app()->getLocale()->isStoreDateInInterval(
            null,
            $page->getData('custom_design_active_from'),
            $page->getData('custom_design_active_to')
        )
        ) {
            $designInfo = explode("/", $page->getData('custom_design'));
            if (count($designInfo) == 2) {
                $this->getDesign()->setPackageName($designInfo[0])->setTheme($designInfo[1]);
            }
            $result .= $page->getData('custom_layout_xml');
        }

        return $result;
    }

    /**
     * @return Mage_Core_Model_Design_Package
     */
    public function getDesign() {
        return Mage::getSingleton('core/design_package');
    }

    /**
     * @return Mage_Page_Helper_Layout
     */
    public function pageLayoutHelper() {
        return $this->getLayout()->helper('page/layout');
    }

    #region Dependencies
    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }

    /**
     * @return Mana_Db_Helper_Data
     */
    public function dbHelper() {
        return Mage::helper('mana_db');
    }

    /**
     * @return Mana_Core_Helper_Db
     */
    public function coreDbHelper() {
        return Mage::helper('mana_core/db');
    }

    /**
     * @return Mage_Adminhtml_Model_Session
     */
    public function getSessionSingleton() {
        return Mage::getSingleton('adminhtml/session');
    }

    /**
     * @return Mana_Core_Helper_Files
     */
    public function fileHelper() {
        return Mage::helper('mana_core/files');
    }
    #endregion

}