<?php
/** 
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Content_Block_Adminhtml_Book_TabContainer extends Mana_Admin_Block_V2_Container {
    public function __construct() {
        parent::__construct();
        $this->setIsTabContainer(true);
        if ($this->getFlatModel()->getId()) {
            if($this->getFlatModel()->getReferenceId()) {
                $this->_headerText = $this->__('%s (Reference) - Book', $this->getFlatModel()->getData('title'));
            } else {
                $this->_headerText = $this->__('%s - Book', $this->getFlatModel()->getData('title'));
            }
        }
        else {
            $this->_headerText = $this->__('New Book');
        }

        $this->adminHelper()->renderSeoSymbols();
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
            $button = $this->getLayout()->createBlock('mana_admin/v2_action', "{$this->getNameInLayout()}.goToOriginal")
                ->setData(array(
                        'label' => $this->__('Go To Original Page'),
                        'class' => 'go',
                        'style' => 'display:none;',
                    ));
            $this->setChild('goToOriginal_button', $button);
            $button = $this->getLayout()->createBlock('mana_admin/v2_action', "{$this->getNameInLayout()}.create")
                ->setData(array(
                        'label' => $this->__('Create Child Page'),
                        'class' => 'add',
                    ));
            $this->setChild('create_button', $button);
            $button = $this->getLayout()->createBlock('mana_admin/v2_action', "{$this->getNameInLayout()}.delete")
                ->setData(array(
                    'label' => $this->__('Delete Current Page'),
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
                'id' => $this->getFlatModel()->getData('page_global_id'),
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
        $id = $this->getRequest()->getParam('id');
        if(!is_null($id) && substr($id, 0, 1) != "n") {
            $id = Mage::getModel('mana_content/page_global')->getCustomSettingId($id);
            $referencePages = Mage::getResourceModel('mana_content/page_globalCustomSettings')->getReferencePages($id);
        } else {
            $referencePages = array();
        }
        /* @var $urlTemplate Mana_Core_Helper_UrlTemplate */
        $urlTemplate = Mage::helper('mana_core/urlTemplate');

        $data = array(
            'type' => 'Mana/Content/Book/TabContainer/'.($this->adminHelper()->isGlobal() ? 'Global' : 'Store'),
            // Add url parameter `isAjax=true` so that magento will return a JSON when a request fails (e.g. Session timeout)
            'save_url' => $urlTemplate->encodeAttribute($this->getStoreSpecificUrl('save') . "?isAjax=true"),
            'close_url' => $urlTemplate->encodeAttribute($this->getUrl('*/mana_content_folder/index',
                $this->adminHelper()->isGlobal() ? array() : array('store' => $this->adminHelper()->getStore()->getId()))),
            'create_url' => $urlTemplate->encodeAttribute($this->getGlobalUrl('create')),
            'delete_url' => $urlTemplate->encodeAttribute($this->getGlobalUrl('delete')),
            'delete_confirm_text' => $this->__('Are you sure you want to delete this page and all its child pages?'),
            'delete_whole_page_text' => $this->__('Delete Whole Page'),
            'delete_reference_page_text' => $this->__('Delete Reference Page'),
            'delete_confirm_root_text' => $this->__('Are you sure you want to delete the whole book? You will be redirected to page list immediately.'),
            // Add url parameter `isAjax=true` so that magento will return a JSON when a request fails (e.g. Session timeout)
            'load_url' => $urlTemplate->encodeAttribute($this->getStoreSpecificUrl('load')."?isAjax=true"),
            // Add url parameter `isAjax=true` so that magento will return a JSON when a request fails (e.g. Session timeout)
            'tree_save_state_url' => $urlTemplate->encodeAttribute($this->getStoreSpecificUrl('saveTreeState')."?isAjax=true"),
            'default_title_text' => Mage::getStoreConfig('mana_content/book/default_title'),
            'default_content_text' => Mage::getStoreConfig('mana_content/book/default_content'),
            'save_mode_text' => Mage::getStoreConfig('mana_content/book/save_mode'),
            'visible_title_char' => Mage::getStoreConfig('mana_content/general/visible_title_char'),
            // Add url parameter `isAjax=true` so that magento will return a JSON when a request fails (e.g. Session timeout)
            'get_record_url' => $urlTemplate->encodeAttribute($this->getUrl('*/*/getRecord')."?isAjax=true"),
            'reference_pages' => json_encode($referencePages),
            'tree_icon_error_url' => $this->getSkinUrl('images/mana_content/tree-icon.png'),
            'wysiwyg_enabled' => Mage::getStoreConfig('cms/wysiwyg/enabled'),
        );

        if (!$this->adminHelper()->isGlobal()) {
            $data = array_merge($data, array(
                'global' => $this->jsonHelper()->encodeAttribute($this->getGlobalFlatModel()->getData()),
                'global_is_custom' => $this->jsonHelper()->encodeAttribute(array(
                    'url_key' => $this->coreDbHelper()->isModelContainsCustomSetting(
                        $this->getGlobalEditModel(), Mana_Content_Model_Page_Abstract::DM_URL_KEY),
                    'meta_title' => $this->coreDbHelper()->isModelContainsCustomSetting(
                        $this->getGlobalEditModel(), Mana_Content_Model_Page_Abstract::DM_META_TITLE),
                    'meta_description' => $this->coreDbHelper()->isModelContainsCustomSetting(
                        $this->getGlobalEditModel(), Mana_Content_Model_Page_Abstract::DM_META_DESCRIPTION),
                    'meta_keywords' => $this->coreDbHelper()->isModelContainsCustomSetting(
                        $this->getGlobalEditModel(), Mana_Content_Model_Page_Abstract::DM_META_KEYWORDS),
                )),
            ));
        }

        $this->setData('m_client_side_block', $data);
        return $this;
    }

    public function getButtonsHtml($area = null) {
        $html = '';
        $html .= $this->getChildHtml('close_button');
        $html .= $this->getChildHtml('goToOriginal_button');
        $html .= $this->getChildHtml('create_button');
        $html .= $this->getChildHtml('delete_button');
        $html .= $this->getChildHtml('apply_button');
        $html .= $this->getChildHtml('save_button');
        $html .= parent::getButtonsHtml($area);
        return $html;
    }

    #region Dependencies
    /**
     * @return Mana_Content_Model_Page_Abstract
     */
    public function getFlatModel() {
        return Mage::registry('m_flat_model');
    }

    /**
     * @return Mana_Content_Model_Page_Abstract
     */
    public function getEditModel() {
        return Mage::registry('m_edit_model');
    }

    /**
     * @return Mana_Content_Model_Page_Abstract
     */
    public function getGlobalFlatModel() {
        return Mage::registry('m_global_flat_model');
    }

    /**
     * @return Mana_Content_Model_Page_Abstract
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