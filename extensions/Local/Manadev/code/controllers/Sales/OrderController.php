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

    public function completeFreeAction() {
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (!empty($orderIds)) {
            foreach (Mage::getResourceModel('sales/order_collection')
                             ->addAttributeToSelect('*')
                             ->addAttributeToFilter('entity_id', array('in' => $orderIds))
                     as $document) {

                /* @var $document Mage_Sales_Model_Order */
                if ($document->getGrandTotal() == 0) {
                    $document->setStatus('complete');
                    $document->save();

                    foreach ($document->getAllItems() as $orderItem) {
                        $licenseCollection = Mage::getResourceModel('downloadable/link_purchased_item_collection');
                        $licenseCollection->addFieldToFilter("order_item_id", array('eq' => $orderItem->getId()));
                        foreach ($licenseCollection->getItems() as $item) {
                            $item->setData('status', Local_Manadev_Model_Download_Status::M_LINK_STATUS_NOT_REGISTERED)
                                ->save();
                        }
                    }

                }
            }
        }
        $this->_getSession()->addSuccess('Documents marked as complete!');
        $this->_redirect('*/*/');
    }
}