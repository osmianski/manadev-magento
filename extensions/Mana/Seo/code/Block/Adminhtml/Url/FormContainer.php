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
class Mana_Seo_Block_Adminhtml_Url_FormContainer extends Mana_Admin_Block_V2_Container {
    public function __construct() {
        parent::__construct();
        $this->setIsTabContainer(true);
        $this->_headerText = $this->__('%s - SEO URL Key', $this->getFlatModel()->getFinalUrlKey());
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
            'id' => $this->getFlatModel()->getId()
        ));
    }

    protected function _prepareClientSideBlock() {
        /* @var $urlTemplate Mana_Core_Helper_UrlTemplate */
        $urlTemplate = Mage::helper('mana_core/urlTemplate');


        $this->setData('m_client_side_block', array(
            'type' => 'Mana/Seo/Url/FormContainer',
            'save_url' => $urlTemplate->encodeAttribute($this->getGlobalUrl('save')),
            'close_url' => $urlTemplate->encodeAttribute($this->getUrl('*/*/index')),
        ));

        return $this;
    }

    public function getButtonsHtml($area = null) {
        $html = '';
        $html .= $this->getChildHtml('close_button');
        $html .= $this->getChildHtml('apply_button');
        $html .= $this->getChildHtml('save_button');
        $html .= parent::getButtonsHtml($area);
        return $html;
    }

    #region Dependencies
    /**
     * @return Mana_Seo_Model_Url
     */
    public function getFlatModel() {
        return Mage::registry('m_flat_model');
    }

    /**
     * @return Mana_Seo_Model_Url
     */
    public function getEditModel() {
        return Mage::registry('m_edit_model');
    }
    #endregion
}