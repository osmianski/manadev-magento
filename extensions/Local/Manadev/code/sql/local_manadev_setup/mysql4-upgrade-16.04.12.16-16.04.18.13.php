<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Mage_Downloadable_Model_Resource_Link_Purchased_Item_Collection $collection */
$collection = Mage::getResourceModel('downloadable/link_purchased_item_collection');
$collection->addFieldToFilter('m_license_no', array('null' => true));

/** @var Local_Manadev_Model_Downloadable_Item $item */
foreach($collection->getItems() as $item) {
    if(!$item->getData('m_license_verification_no')) {
        $item->setData('m_license_verification_no', uniqid());
    }
    if(!$item->getData('m_license_no', uniqid())) {
        $item->setData('m_license_no', uniqid());
    }
    $item->save();
}

$installer->endSetup();