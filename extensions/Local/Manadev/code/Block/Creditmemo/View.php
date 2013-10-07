<?php

class Local_Manadev_Block_Creditmemo_View extends Mage_Adminhtml_Block_Sales_Order_Creditmemo_View {
	public function __construct() {
		parent::__construct();
		
		$this->_addButton('print_lt', array(
        	'label'     => 'Print (LT)',
            'class'     => 'save',
            'onclick'   => 'setLocation(\''.$this->getUrl('*/sales_creditmemo/printLt', array(
		            'creditmemo_id' => $this->getCreditmemo()->getId()
		        )).'\')'
        ));
		
		$this->_addButton('m_bill_to', array(
                'label'     => 'Bill To',
                'onclick'   => 'setLocation(\''.$this->getUrl('*/sales_billto/edit', array(
                	'creditmemo_id' => $this->getCreditmemo()->getId(),
					'redirect_to' => Mage::helper('core')->urlEncode($this->getUrl('*/*/*', array('_current' => true)))
				)).'\')'
                )
            );

        $this->updateButton('print', 'label', 'Print (EN)');     
	}
    public function getPrintUrl()
    {
        return $this->getUrl('*/sales_creditmemo/printEn', array(
            'creditmemo_id' => $this->getCreditmemo()->getId()
        ));
    }
}