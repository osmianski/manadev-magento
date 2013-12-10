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
class Mana_AttributePage_Adminhtml_Mana_AttributePageController extends Mana_Admin_Controller_V2_Controller  {
    protected function _registerModels() {
        if (!($customSettings = Mage::registry('m_edit_model'))) {
            if ($this->adminHelper()->isGlobal()) {
                $customSettings = Mage::getModel('mana_attributepage/attributePage_globalCustomSettings');
                $finalSettings = Mage::getModel('mana_attributepage/attributePage_global');
                $finalSettings->setData('_add_option_page_defaults', true);
                if ($id = $this->getRequest()->getParam('id')) {
                    $finalSettings->load($id);
                    if (!$finalSettings->getId()) {
                        throw new Mage_Core_Exception($this->__('This attribute page no longer exists.'));
                    }
                    $customSettings->load($finalSettings->getData('attribute_page_global_custom_settings_id'));
                }
                else {
                    $finalSettings->setDefaults();
                }
            }
            else {
                if ($id = $this->getRequest()->getParam('id')) {
                    $customSettings = Mage::getModel('mana_attributepage/attributePage_storeCustomSettings');
                    $finalSettings = Mage::getModel('mana_attributepage/attributePage_store');
                    $finalSettings->setData('store_id', $this->adminHelper()->getStore()->getId());
                    $finalSettings->load($id, 'attribute_page_global_id');
                    if ($customSettingsId = $finalSettings->getData('attribute_page_store_custom_settings_id')) {
                        $customSettings->load($customSettingsId);
                    }
                }
                else {
                    throw new Mage_Core_Exception($this->__('Non existent attribute pages can not be customized on store level.'));
                }
            }
            Mage::register('m_edit_model', $customSettings);
            Mage::register('m_flat_model', $finalSettings);
        }
        else {
            $finalSettings = Mage::registry('m_flat_model');
        }

        return compact('customSettings', 'finalSettings');
    }

    public function indexAction() {
        // page
        $this->_title('Mana')->_title($this->__('Attribute Pages'));

        // layout
        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();

        if (!Mage::app()->isSingleStoreMode()) {
            $update->addHandle('mana_admin2_multistore_list');
        }
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;
        $this->_initLayoutMessages('adminhtml/session');

        // rendering
        $this->_setActiveMenu('mana/attributepage');
        $this->renderLayout();
    }

    public function gridAction() {
        // layout
        $this->addActionLayoutHandles();
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;

        // render AJAX result
        $this->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {
        try {
            $models = $this->_registerModels();
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/');
            return;
        }

        /* @var $model Mana_AttributePage_Model_AttributePage_Abstract */
        $model = $models['finalSettings'];

        // page
        if ($model->getId()) {
            $this->_title('Mana')->_title($this->__('%s - Attribute Page', $model->getData('title')));
        }
        else {
            $this->_title('Mana')->_title($this->__('New Attribute Page'));
        }

        // layout
        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();

        if ($model->getId() && !Mage::app()->isSingleStoreMode()) {
            $update->addHandle('mana_admin2_multistore_card');
        }
        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;
        $this->_initLayoutMessages('adminhtml/session');

        // simplify if one tab
        if (($tabs = $this->getLayout()->getBlock('tabs')) && count($tabs->getChild()) == 1) {
            /* @var $tabs Mana_Admin_Block_V2_Tabs */
            $content = $tabs->getActiveTabBlock();
            $tabs->getParentBlock()->unsetChild('tabs');
            $this->getLayout()->getBlock('container')->insert($content, $content->getNameInLayout(), null, $content->getNameInLayout());
            $content->addToParentGroup('content');
        }

        // rendering
        $this->_setActiveMenu('mana/attributepage');
        $this->renderLayout();
     }
}