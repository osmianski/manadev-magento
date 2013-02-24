<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterHelp
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterAdvanced_Block_Tab extends Mage_Adminhtml_Block_Text_List implements Mage_Adminhtml_Block_Widget_Tab_Interface {
    ///////////////////////////////////////////////////////
    // TAB PROPERTIES
    ///////////////////////////////////////////////////////

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel() {
        return $this->__('Advanced');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle() {
        return $this->__('Advanced');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab() {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden() {
        return false;
    }

    public function getAjaxUrl() {
        return Mage::helper('mana_admin')->getStoreUrl('*/*/tabAdvanced',
            array('id' => Mage::app()->getRequest()->getParam('id')),
            array('ajax' => 1)
        );
    }
}