<?php

require_once(Mage::getModuleDir('controllers', 'Mage_Sales') . DS . 'OrderController.php');

class Local_Manadev_FrontendSales_OrderController extends Mage_Sales_OrderController {
    protected function _viewAction()
    {
        if (!$this->_loadValidOrder()) {
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        if (Mage::getStoreConfig('web/default/show_cms_breadcrumbs')
            && ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs'))
        ) {
            $breadcrumbs->addCrumb('my_orders', array(
                'label' => Mage::helper('cms')->__('My Orders'),
                'title' => Mage::helper('cms')->__('My Orders'),
                'link' => Mage::getUrl('sales/order/history')
            ));
            $breadcrumbs->addCrumb('order', array(
                'label' => Mage::helper('cms')->__('Order No ' . Mage::registry('current_order')->getRealOrderId() ),
                'title' => Mage::helper('cms')->__('Order No ' . Mage::registry('current_order')->getRealOrderId())
            ));
        }

        $this->renderLayout();
    }
}