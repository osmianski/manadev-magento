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
class ManaPro_FilterContent_Block_Adminhtml_TabContainer  extends Mana_Admin_Block_V2_Container {
    public function __construct() {
        parent::__construct();
        $this->setIsTabContainer(true);
        if ($this->getFlatModel()->getId()) {
            $this->_headerText = $this->__('Filter Specific Content Applied When %s', $this->getFlatModel()->getData('title'));
        }
        else {
            $this->_headerText = $this->__('New Filter Specific Content');
        }
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

        if ($this->getFlatModel()->getId() && $this->adminHelper()->isGlobal()) {
            $button = $this->getLayout()->createBlock('mana_admin/v2_action', "{$this->getNameInLayout()}.delete")
                ->setData(array(
                    'label' => $this->__('Delete'),
                    'class' => 'delete',
                ));
            $this->setChild('delete_button', $button);
        }

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
                'id' => $this->getFlatModel()->getId()
            ));
        }
        else {
            return $this->adminHelper()->getStoreUrl('*/*/'. $action, array(
                'id' => $this->getFlatModel()->getData('attribute_page_global_id'),
                'store' => $this->adminHelper()->getStore()->getId()
            ));
        }
    }

    public function getGlobalUrl($action) {
        return $this->adminHelper()->getStoreUrl('*/*/' . $action, array(
            'id' => $this->getFlatModel()->getId()
        ));
    }

    protected function _prepareClientSideBlock() {
        /* @var $urlTemplate Mana_Core_Helper_UrlTemplate */
        $urlTemplate = Mage::helper('mana_core/urlTemplate');


        $data = array(
            'type' => 'ManaPro/FilterContent/TabContainer/'.($this->adminHelper()->isGlobal() ? 'Global' : 'Store'),
            'save_url' => $urlTemplate->encodeAttribute($this->getStoreSpecificUrl('save')),
            'close_url' => $urlTemplate->encodeAttribute($this->getUrl('*/*/index',
                $this->adminHelper()->isGlobal() ? array() : array('store' => $this->adminHelper()->getStore()->getId()))),
            'delete_url' => $urlTemplate->encodeAttribute($this->getGlobalUrl('delete')),
            'delete_confirm_text' => $this->__('Are you sure you want to delete this filter specific content and all related option pages?'),
        );

        $this->setData('m_client_side_block', $data);
        return $this;
    }

    public function getButtonsHtml($area = null) {
        $html = '';
        $html .= $this->getChildHtml('close_button');
        $html .= $this->getChildHtml('delete_button');
        $html .= $this->getChildHtml('apply_button');
        $html .= $this->getChildHtml('save_button');
        $html .= parent::getButtonsHtml($area);
        return $html;
    }

    #region Dependencies
    /**
     * @return ManaPro_FilterContent_Model_Abstract
     */
    public function getFlatModel() {
        return Mage::registry('m_flat_model');
    }

    /**
     * @return ManaPro_FilterContent_Model_Abstract
     */
    public function getEditModel() {
        return Mage::registry('m_edit_model');
    }

    /**
     * @return ManaPro_FilterContent_Model_Abstract
     */
    public function getGlobalFlatModel() {
        return Mage::registry('m_global_flat_model');
    }

    /**
     * @return ManaPro_FilterContent_Model_Abstract
     */
    public function getGlobalEditModel() {
        return Mage::registry('m_global_edit_model');
    }
    /**
     * @return Mana_Core_Helper_StringTemplate
     */
    public function templateHelper() {
        return Mage::helper('mana_core/stringTemplate');
    }
    #endregion
}