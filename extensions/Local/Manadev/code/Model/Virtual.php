<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Model_Virtual extends Mage_Catalog_Model_Product_Type_Virtual
{

    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode) {
        $products = parent::_prepareProduct($buyRequest, $product, $processMode);

        if(Mage::getStoreConfig('local_manadev/support_services_sku') != $product->getSku()) {
            return $products;
        }

        $product = reset($products);

        $item = Mage::getModel('downloadable/link_purchased_item')->load($buyRequest->getData('m_license'));
        if($item->getId()) {
            $additionalOptions = array();
            $additionalOptions[] = array(
                'label' => "For License",
                'value' => $item->getFrontendLabel(),
            );

            $product->addCustomOption('additional_options', serialize($additionalOptions));
        }

        return array($product);
    }

    public function getOrderOptions($product = null) {
        $optionArr = parent::getOrderOptions($product);

        if($additional_options = $this->getProduct($product)->getCustomOption('additional_options')) {
            $optionArr['additional_options'] = unserialize($additional_options->getValue());
        }

        return $optionArr;
    }
}