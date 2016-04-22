<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Adminhtml_Renderer_Order extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected function _getValue(Varien_Object $row) {
        $url = $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getData('order_id')));
        $customerName = parent::_getValue($row);
        $html = "<a href='{$url}'>{$customerName}</a>";

        return $html;
    }
}