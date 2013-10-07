<?php

include_once 'app/code/core/Mage/Adminhtml/controllers/Sales/OrderController.php';

/**
 * Additional invoice actions
 * @author Mana Team
 *
 */
class Local_Manadev_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController {
    public function recalculateAction() {
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (!empty($orderIds)) {
            foreach (Mage::getResourceModel('sales/order_collection')
                             ->addAttributeToSelect('*')
                             ->addAttributeToFilter('entity_id', array('in' => $orderIds))
                     as $document) {
                Mage::helper('local_manadev')->recalculateDocument($document);
                $document->save();
            }
        }
        $this->_getSession()->addSuccess('Documents recalculated successfully!');
        $this->_redirect('*/*/');
    }
}