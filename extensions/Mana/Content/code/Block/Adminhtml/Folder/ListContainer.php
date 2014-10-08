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
class Mana_Content_Block_Adminhtml_Folder_ListContainer extends Mana_Admin_Block_V2_Container {
    public function __construct() {
        parent::__construct();
        $this->_headerText = $this->__('Content Management');
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();

        /* @var $button Mana_Admin_Block_Grid_Action */
        $button = $this->getLayout()->createBlock('mana_admin/v2_action', "{$this->getNameInLayout()}.create_book")
            ->setData(array(
                'label' => $this->__('Create Book'),
                'class' => 'add',
            ));
        $this->setChild('create_book_button', $button);

        // create client-side block
        $this->_prepareClientSideBlock();

        return $this;
    }

    protected function _prepareClientSideBlock() {
        /* @var $urlTemplate Mana_Core_Helper_UrlTemplate */
        $urlTemplate = Mage::helper('mana_core/urlTemplate');


        $this->setData('m_client_side_block', array(
            'type' => 'Mana/Content/Folder/ListContainer',
            'create_book_url' => $urlTemplate->encodeAttribute($this->getUrl('*/mana_content_book/new')),
            'create_feed_url' => $urlTemplate->encodeAttribute($this->getUrl('*/mana_content_list/new')),
        ));

        return $this;
    }
    public function getButtonsHtml($area = null) {
        $html = '';
        $html .= $this->getChildHtml('create_book_button');
        $html .= $this->getChildHtml('create_feed_button');
        $html .= parent::getButtonsHtml($area);
        return $html;
    }
}