<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Adminhtml_Renderer_Customer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected function _getValue(Varien_Object $row) {
        $url = $this->getUrl('adminhtml/customer/edit', array('id' => $row->getData('customer_id')));
        $customerName = parent::_getValue($row);
        $html = "<a href='{$url}'>{$customerName}</a>";
        if($customerName == $this->localHelper()->getLoggedNotInLabel()) {
            $html = $customerName;
        }

        return $html;
    }

    /**
     * @return Local_Manadev_Helper_Data
     */
    protected function localHelper() {
        return Mage::helper('local_manadev');
    }
}