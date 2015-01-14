<?php
/** 
 * @category    Mana
 * @package     Mana_Sorting
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Sorting_Block_Adminhtml_Method_ListContainer extends Mana_Admin_Block_V2_Container {
    public function __construct() {
        parent::__construct();
        $this->_headerText = $this->__('Sorting Method Management');
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();

        /* @var $button Mana_Admin_Block_Grid_Action */
        $button = $this->getLayout()->createBlock('mana_admin/v2_action', "{$this->getNameInLayout()}.create_method")
            ->setData(array(
                'label' => $this->__('Create Sort Method'),
                'class' => 'add',
            ));
        $this->setChild('create_method_button', $button);

        // create client-side block
        $this->_prepareClientSideBlock();

        return $this;
    }

    protected function _prepareClientSideBlock() {
        /* @var $urlTemplate Mana_Core_Helper_UrlTemplate */
        $urlTemplate = Mage::helper('mana_core/urlTemplate');


        $this->setData('m_client_side_block', array(
            'type' => 'Mana/Sorting/Method/ListContainer',
            'create_method_url' => $urlTemplate->encodeAttribute($this->getUrl('*/*/new')),
        ));

        return $this;
    }
    public function getButtonsHtml($area = null) {
        $html = '';
        $html .= $this->getChildHtml('create_method_button');
        $html .= parent::getButtonsHtml($area);
        return $html;
    }
}