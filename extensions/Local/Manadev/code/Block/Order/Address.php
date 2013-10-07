<?php

class Local_Manadev_Block_Order_Address extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId = 'entity_id';
        $this->_controller = 'order';
        $this->_blockGroup = 'local_manadev';
        $this->_mode = 'address';
        
    	parent::__construct();

        //$this->_updateButton('save', 'label', 'Save');
        $this->setFormActionUrl($this->getUrl('*/*/save'));
        $this->_removeButton('reset');
        $this->_removeButton('back');
    }

    public function getHeaderText()
    {
    	return sprintf('Edit Billing Address for Order No %s, %s No %s', Mage::registry('order')->getIncrementId(), 
    		Mage::registry('document_type_name'), 
    		Mage::registry('document')->getIncrementId());
    }
}
