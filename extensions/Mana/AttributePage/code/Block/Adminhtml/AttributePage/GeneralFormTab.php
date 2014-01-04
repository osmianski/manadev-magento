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
class Mana_AttributePage_Block_Adminhtml_AttributePage_GeneralFormTab extends Mana_Admin_Block_V2_Tab  {
    protected function _prepareLayout() {
        parent::_prepareLayout();
        if ($this->getFlatModel()->getId()) {
            $this->setData('active', true);
        }
    }

    public function getTitle() {
        return $this->__('General');
    }

    public function getGroup() {
        return $this->__('Attribute Page');
    }

    public function getAjaxUrl() {
        $id = Mage::app()->getRequest()->getParam('id');
        return $this->adminHelper()->getStoreUrl('*/*/tabGeneral',
            $id ? compact('id') : array(),
            array('ajax' => 1)
        );
    }
    #region Dependencies
    /**
     * @return Mana_AttributePage_Model_AttributePage_Abstract
     */
    public function getFlatModel() {
        return Mage::registry('m_flat_model');
    }
    #endregion
}