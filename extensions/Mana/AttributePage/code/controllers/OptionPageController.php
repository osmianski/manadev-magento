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

        /* @var $optionPage Mana_AttributePage_Model_OptionPage_Store */
        $optionPage = Mage::getModel('mana_attributepage/optionPage_store');
        $optionPage->setData('store_id', Mage::app()->getStore()->getId());
        $optionPage->load($optionPageId);

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

        /* @var $attributePage Mana_AttributePage_Model_AttributePage_Store */
        $attributePage = Mage::getModel('mana_attributepage/attributePage_store');
        $attributePage->setData('store_id', Mage::app()->getStore()->getId());
        $attributePage->load($optionPage->getData('attribute_page_global_id'), 'attribute_page_global_id');

        Mage::register('current_attribute_page', $attributePage );

        return $optionPage;
    }
}