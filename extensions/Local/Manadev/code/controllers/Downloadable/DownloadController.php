<?php
require_once(Mage::getModuleDir('controllers', 'Mage_Downloadable') . DS . 'DownloadController.php');
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Downloadable_DownloadController extends Mage_Downloadable_DownloadController
{
    public function linkAction(){
        $id = $this->getRequest()->getParam('id', 0);
        $linkPurchasedItem = Mage::getModel('downloadable/link_purchased_item')->load($id, 'link_hash');
        if ($linkPurchasedItem->getId() ) {
            Mage::helper('local_manadev')->createNewZipFileWithLicense($linkPurchasedItem);
            $linkPurchasedItem->save();
        }

        return parent::linkAction();
    }
}