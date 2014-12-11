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
class ManaPro_FilterContent_Block_Adminhtml_ConditionFormTab extends Mana_Admin_Block_V2_Tab
{
    public function getTitle() {
        return $this->__('Condition');
    }

    public function getAjaxUrl() {
        $id = Mage::app()->getRequest()->getParam('id');

        return $this->adminHelper()->getStoreUrl(
            '*/*/tabCondition',
            $id ? compact('id') : array(),
            array('ajax' => 1)
        );
    }
}