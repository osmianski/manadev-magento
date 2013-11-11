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
class Mana_AttributePage_OptionPageController extends Mage_Core_Controller_Front_Action {
    public function viewAction() {
        if ($this->_initOptionPage()) {
            Mage::getSingleton('mana_attributepage/layer')->apply();
            $this->loadLayout();
            $this->renderLayout();
        }
        else {
            $this->_forward('noRoute');
        }
    }

    protected function _initOptionPage() {
        Mage::dispatchEvent('mana_attributepage_controller_option_page_init_before', array('controller_action' => $this));
        $optionPageId = (int) $this->getRequest()->getParam('id', false);
        if (!$optionPageId) {
            return false;
        }

        /* @var $optionPage Mana_AttributePage_Model_Option_Page */
        $optionPage = Mage::helper('mana_db')->getModel('mana_attributepage/option_page/store_flat');
        $optionPage->loadForStore($optionPageId, Mage::app()->getStore()->getId());

        if (!$optionPage->canShow()) {
            return false;
        }
        Mage::register('current_option_page', $optionPage);
        try {
            Mage::dispatchEvent('mana_attributepage_controller_option_page_init_after', array('option_page' => $optionPage, 'controller_action' => $this));
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return false;
        }

        /* @var $attributePage Mana_AttributePage_Model_Page */
        $attributePage = Mage::helper('mana_db')->getModel('mana_attributepage/page/store_flat');
        $attributePage->loadForStore($optionPage->getData('attribute_page_id'), Mage::app()->getStore()->getId());
        if (!$attributePage->canShow()) {
            return false;
        }

        Mage::register('current_attribute_page', $attributePage );

        return $optionPage;
    }
}