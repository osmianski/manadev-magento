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
class Mana_Seo_Block_Adminhtml_Schema_TabContainer extends Mana_Admin_Block_V2_Container {
    public function __construct() {
        parent::__construct();
        $this->setIsTabContainer(true);
        $this->_headerText = $this->__('%s - SEO Schema', $this->getFlatModel()->getName());
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();

        /* @var $button Mana_Admin_Block_Grid_Action */
        $button = $this->getLayout()->createBlock('mana_admin/v2_action', "{$this->getNameInLayout()}.close")
            ->setData(array(
                'label' => $this->__('Close'),
                'class' => 'back',
            ));
        $this->setChild('close_button', $button);

        $button = $this->getLayout()->createBlock('mana_admin/v2_action', "{$this->getNameInLayout()}.duplicate")
            ->setData(array(
                'label' => $this->__('Duplicate'),
                'class' => 'add'.($this->adminHelper()->isGlobal() ? '' : ' disabled'),
            ));
        $this->setChild('duplicate_button', $button);

        $button = $this->getLayout()->createBlock('mana_admin/v2_action', "{$this->getNameInLayout()}.delete")
            ->setData(array(
                'label' => $this->__('Delete'),
                'class' => 'delete' . ($this->adminHelper()->isGlobal() &&
                    $this->getFlatModel()->getStatus() != Mana_Seo_Model_Schema::STATUS_ACTIVE ? '' : ' disabled'),
            ));
        $this->setChild('delete_button', $button);

        $button = $this->getLayout()->createBlock('mana_admin/v2_action', "{$this->getNameInLayout()}.apply")
            ->setData(array(
                'label' => $this->__('Apply'),
                'class' => 'save',
            ));
        $this->setChild('apply_button', $button);

        $button = $this->getLayout()->createBlock('mana_admin/v2_action', "{$this->getNameInLayout()}.save")
            ->setData(array(
                'label' => $this->__('Save'),
                'class' => 'save',
            ));
        $this->setChild('save_button', $button);

        // create client-side block
        $this->_prepareClientSideBlock();

        return $this;

    }

    public function getStoreSpecificUrl($action) {
        if ($this->adminHelper()->isGlobal()) {
            return $this->adminHelper()->getStoreUrl('*/*/'. $action, array(
                'id' => $this->getFlatModel()->getPrimaryId()
            ));
        }
        else {
            return $this->adminHelper()->getStoreUrl('*/*/'. $action, array(
                'id' => $this->getFlatModel()->getPrimaryGlobalId(),
                'store' => $this->adminHelper()->getStore()->getId()
            ));
        }
    }

    public function getGlobalUrl($action) {
        return $this->adminHelper()->getStoreUrl('*/*/' . $action, array(
            'id' => $this->getFlatModel()->getPrimaryId()
        ));
    }

    protected function _prepareClientSideBlock() {
        /* @var $urlTemplate Mana_Core_Helper_UrlTemplate */
        $urlTemplate = Mage::helper('mana_core/urlTemplate');


        $this->setData('m_client_side_block', array(
            'type' => 'Mana/Seo/Schema/TabContainer',
            'save_url' => $urlTemplate->encodeAttribute($this->getStoreSpecificUrl('save')),
            'close_url' => $urlTemplate->encodeAttribute($this->getUrl('*/*/index',
                $this->adminHelper()->isGlobal() ? array() : array('store' => $this->adminHelper()->getStore()->getId()))),
            'delete_url' => $urlTemplate->encodeAttribute($this->getGlobalUrl('delete')),
            'duplicate_url' => $urlTemplate->encodeAttribute($this->getGlobalUrl('duplicate')),
            'before_save_url' => $urlTemplate->encodeAttribute($this->getStoreSpecificUrl('beforeSave')),
            'hide_create_seo_schema_duplicate_advice_message_url' =>
                $urlTemplate->encodeAttribute($this->getUrl('*/mana/hideMessage',
                array('message_key' => 'create_seo_schema_duplicate_advice'))),
            'delete_confirm_text' => $this->__('Are you sure you want to delete this SEO schema?'),
        ));

        return $this;
    }

    public function getButtonsHtml($area = null) {
        $html = '';
        $html .= $this->getChildHtml('close_button');
        $html .= $this->getChildHtml('duplicate_button');
        $html .= $this->getChildHtml('delete_button');
        $html .= $this->getChildHtml('apply_button');
        $html .= $this->getChildHtml('save_button');
        $html .= parent::getButtonsHtml($area);
        return $html;
    }

    #region Dependencies
    /**
     * @return Mana_Seo_Model_Schema
     */
    public function getFlatModel() {
        return Mage::registry('m_flat_model');
    }

    /**
     * @return Mana_Seo_Model_Schema
     */
    public function getEditModel() {
        return Mage::registry('m_edit_model');
    }
    #endregion
}