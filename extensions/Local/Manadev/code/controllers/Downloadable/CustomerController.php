<?php
require_once(Mage::getModuleDir('controllers', 'Mage_Downloadable') . DS . 'CustomerController.php');
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Downloadable_CustomerController extends Mage_Downloadable_CustomerController
{

    /**
     * Display downloadable links bought by customer
     *
     */
    public function productsAction() {

        $this->loadLayout();
        if($download = $this->getRequest()->getParam('download')) {
            $block = $this->getLayout()->addBlock('Local_Manadev_Block_Download', 'download');
            $this->getLayout()->getBlock('content')->insert($block);
        }
        $this->_initLayoutMessages('customer/session');
        if ($block = $this->getLayout()->getBlock('downloadable_customer_products_list')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle(Mage::helper('local_manadev')->__('My Licenses and Downloads'));
        }
        $this->renderLayout();
    }
}