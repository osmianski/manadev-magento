<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Seo_Block_Adminhtml_Schema_UrlFormTab extends Mana_Admin_Block_V2_Tab {
    public function getTitle() {
        return $this->__('URL Settings');
    }

    public function getAjaxUrl() {
        return $this->adminHelper()->getStoreUrl('*/*/tabUrl',
            array('id' => Mage::app()->getRequest()->getParam('id')),
            array('ajax' => 1)
        );
    }
}