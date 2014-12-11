<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterContent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterContent_Block_Adminhtml_Filter_ContentTab extends Mana_Admin_Block_V2_Tab
{
    public function getTitle() {
        return $this->__('Filter Specific Content');
    }

    public function getAjaxUrl() {
        $id = Mage::app()->getRequest()->getParam('id');

        return $this->adminHelper()->getStoreUrl(
            '*/*/tabContent',
            $id ? compact('id') : array(),
            array('ajax' => 1)
        );
    }
    public function isHidden() {
    	return Mage::registry('m_crud_model')->getType() != 'attribute';
    }
}