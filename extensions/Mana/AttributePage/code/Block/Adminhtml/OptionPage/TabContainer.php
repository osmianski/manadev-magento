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
class Mana_AttributePage_Block_Adminhtml_OptionPage_TabContainer extends Mana_Admin_Block_V2_Container {
    public function __construct() {
        parent::__construct();
        $this->setIsTabContainer(true);
        $this->_headerText = $this->__('%s - Option Page', $this->getFlatModel()->getData('title'));
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
                'parent_id' => Mage::app()->getRequest()->getParam('parent_id'),
                'id' => $this->getFlatModel()->getId()
            ));
        }
        else {
            return $this->adminHelper()->getStoreUrl('*/*/'. $action, array(
                'parent_id' => Mage::app()->getRequest()->getParam('parent_id'),
                'id' => $this->getFlatModel()->getData('option_page_global_id'),
                'store' => $this->adminHelper()->getStore()->getId()
            ));
        }
    }

    protected function _prepareClientSideBlock() {
        /* @var $urlTemplate Mana_Core_Helper_UrlTemplate */
        $urlTemplate = Mage::helper('mana_core/urlTemplate');


        $data = array(
            'type' => 'Mana/AttributePage/OptionPage/TabContainer/'.($this->adminHelper()->isGlobal() ? 'Global' : 'Store'),
            'save_url' => $urlTemplate->encodeAttribute($this->getStoreSpecificUrl('save')),
            'close_url' => $urlTemplate->encodeAttribute($this->getUrl('*/*/index',
                $this->adminHelper()->isGlobal()
                    ? array(
                        'parent_id' => Mage::app()->getRequest()->getParam('parent_id'),
                    )
                    : array(
                        'parent_id' => Mage::app()->getRequest()->getParam('parent_id'),
                        'store' => $this->adminHelper()->getStore()->getId()
                    )
            )),
            'title_template' => $this->jsonHelper()->encodeAttribute(array(
                'template' => $this->templateHelper()->parse(Mage::getStoreConfig('mana_attributepage/option_page_title/template')),
                'separator' => Mage::getStoreConfig('mana_attributepage/option_page_title/separator'),
                'last_separator' => Mage::getStoreConfig('mana_attributepage/option_page_title/last_separator'),
            )),
            'attribute_page' => $this->jsonHelper()->encodeAttribute($this->getAttributePage()->getData()),
        );

        if (!$this->adminHelper()->isGlobal()) {
            $isCustom = array();
            foreach (array_keys($this->getGlobalFlatModel()->getData()) as $field) {
                if (($bitNo = $this->coreDbHelper()->getModelFieldBitNo($this->getGlobalEditModel(), $field)) !== null) {
                    $isCustom[$field] = $this->coreDbHelper()->isModelContainsCustomSetting($this->getGlobalEditModel(), $bitNo);
                }
            }
            $data = array_merge($data, array(
                'global' => $this->jsonHelper()->encodeAttribute($this->getGlobalFlatModel()->getData()),
                'global_is_custom' => $this->jsonHelper()->encodeAttribute($isCustom),
            ));
        }

        $this->setData('m_client_side_block', $data);
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
     * @return Mana_AttributePage_Model_OptionPage_Abstract
     */
    public function getFlatModel() {
        return Mage::registry('m_flat_model');
    }

    /**
     * @return Mana_AttributePage_Model_OptionPage_Abstract
     */
    public function getEditModel() {
        return Mage::registry('m_edit_model');
    }

    /**
     * @return Mana_AttributePage_Model_OptionPage_Abstract
     */
    public function getGlobalFlatModel() {
        return Mage::registry('m_global_flat_model');
    }

    /**
     * @return Mana_AttributePage_Model_OptionPage_Abstract
     */
    public function getGlobalEditModel() {
        return Mage::registry('m_global_edit_model');
    }
    /**
     * @return Mana_AttributePage_Model_AttributePage_Abstract
     */
    public function getAttributePage() {
        return Mage::registry('m_attribute_page');
    }

    /**
     * @return Mana_AttributePage_Model_AttributePage_Abstract
     */
    public function getGlobalAttributePage() {
        return Mage::registry('m_global_attribute_page');
    }
    /**
     * @return Mana_Core_Helper_StringTemplate
     */
    public function templateHelper() {
        return Mage::helper('mana_core/stringTemplate');
    }
    #endregion
}