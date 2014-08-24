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
class Mana_AttributePage_AttributePageController extends Mage_Core_Controller_Front_Action {
    public function viewAction() {

        if ($attributePage = $this->_initAttributePage()) {
            $layoutXml = $this->_applyCustomDesign();

            $this->getLayout()->getUpdate()->addHandle('default');
            $this->addActionLayoutHandles();
            Mage::helper('mana_core/layout')->addRecursiveLayoutUpdates($layoutXml);
            $this->loadLayoutUpdates();
            if (trim($layoutXml)) {
                $this->getLayout()->getUpdate()->addUpdate($layoutXml);
            }
            $this->generateLayoutXml();
            $this->generateLayoutBlocks();
            $this->_isLayoutLoaded = true;
            if ($pageLayout = $attributePage->getData('page_layout')) {
                $this->pageLayoutHelper()->applyTemplate($pageLayout);
            }
            if ($root = $this->getLayout()->getBlock('root')) {
                /* @var $root Mage_Page_Block_Html */
                $root->addBodyClass('m-'.Mage::getStoreConfig('mana_attributepage/attribute_page_settings/template'));
            }
            $this->renderLayout();
        } else {
            $this->_forward('noRoute');
        }

    }

    protected function _initAttributePage() {
        Mage::dispatchEvent('mana_attributepage_controller_attribute_page_init_before', array('controller_action' => $this));
        $attributePageId = (int) $this->getRequest()->getParam('id', false);
        if (!$attributePageId) {
            return false;
        }

        /* @var $attributePage Mana_AttributePage_Model_AttributePage_Store */
        $attributePage = Mage::getModel('mana_attributepage/attributePage_store');
        $attributePage->setData('store_id', Mage::app()->getStore()->getId());
        $attributePage->load($attributePageId);

        if (!$attributePage->canShow()) {
            return false;
        }
        Mage::register('current_attribute_page', $attributePage);
        try {
            Mage::dispatchEvent('mana_attributepage_controller_attribute_page_init_after', array('attribute_page' => $attributePage, 'controller_action' => $this));
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return false;
        }

        return $attributePage;
    }

     protected function _applyCustomDesign()
    {
        /* @var $page Mana_AttributePage_Model_OptionPage_Store */
        $page = Mage::registry('current_attribute_page');

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

}