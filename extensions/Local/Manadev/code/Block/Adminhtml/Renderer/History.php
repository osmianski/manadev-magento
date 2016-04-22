<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Adminhtml_Renderer_History extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected function _getValue(Varien_Object $row) {
        $filter = 'magento_id='.$row->getData('magento_id');
        $filter = base64_encode($filter);
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/license/magentoInstanceHistory', array('filter'=>$filter));
        $label = Mage::helper('local_manadev')->__("See History...");
        $html = "<a href='{$url}'>{$label}</a>";

        return $html;
    }
}