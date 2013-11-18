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
        if ($this->_initAttributePage()) {
            $this->loadLayout();
            $this->renderLayout();
        }
        else {
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
}