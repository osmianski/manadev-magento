<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_ExtensionController extends Mage_Core_Controller_Front_Action
{
    public function updateAction(){
        $params = $this->getRequest()->getParams();

        Mage::log($params, Zend_Log::DEBUG, 'manadev-client.log', true);
        $signature = $params['magentoInstanceSignature'];
        $key = $params['magentoInstancePublicKey'];

        unset($params['magentoInstancePublicKey']);
        unset($params['magentoInstanceSignature']);

        /** @var Local_Manadev_Model_Key $keyModel */
        $keyModel = Mage::getSingleton('local_manadev/key');

        if($keyModel->verifySignatureFromAvailableKeys($keyModel->dataToString($params), $signature, $key)) {
            /** @var Local_Manadev_Model_License_Instance $instanceModel */
            $instanceModel = Mage::getModel('local_manadev/license_instance')->load($params['magentoInstanceId'], 'magento_id');

            if(!$instanceModel->getId()) {
                $instanceModel->setData('magento_id', $params['magentoInstanceId']);
            }
            $instanceModel->setData('admin_url', $params['adminPanelUrl'])
                ->setData('remote_ip', $params['remoteIp'])
                ->setData('base_dir', $params['baseDir'])
                ->setData('magento_version', $params['magentoVersion']);

            $instanceModel->setModules($params['installedModules']);
            $instanceModel->setExtensions($params['installedManadevExtensions']);
            $instanceModel->setStores($params['stores']);
            $instanceModel->save();

            $result = array(
                'latestManadevExtensionVersions' => $this->_getLatestVersionOfExtensions($params['installedManadevExtensions']),
            );

            $newSignature = $keyModel->generateSignatureFromAvailableKeys($keyModel->dataToString($result), $key);

            $result = array_merge($result, array(
                'manadevStorePublicKey' => $key,
                'manadevStoreSignature' => $newSignature,
            ));
            echo $keyModel->dataToStringSerialize($result);
        } else {
            // Invalid data
            die("invalid key");
        }
    }

    public function testAction() {
        /** @var Mana_Core_Model_Observer $observer */
        $observer = Mage::getModel('mana_core/observer');
        $observer->getLatestExtensionVersionNumbers();
    }

    protected function _getLatestVersionOfExtensions($installedManadevExtensions) {
        $result = array();
        foreach($installedManadevExtensions as $extension) {
            $itemModel = Mage::getModel('downloadable/link_purchased_item')->load($extension['license_verification_no'], 'm_license_verification_no');
            $licenseNo = $itemModel->getData('m_license_no');
            if(!$licenseNo && $itemModel->getId()) {
                $licenseNo = uniqid();
                $itemModel->setData('m_license_no', $licenseNo);
                $itemModel->save();
            }
            if(is_null($licenseNo)) {
                $licenseNo = '';
                $version = $extension['version'];
            } else {
                $version = Mage::getModel('local_manadev/key')->getVersionFromZipFile($itemModel->getLinkFile());
            }

            $sku = $extension['code'];
            $productResource = Mage::getResourceModel('catalog/product');
            $storeId = Mage::app()->getStore()->getId();
            $main_module = $productResource->getAttributeRawValue($productResource->getIdBySku($sku), 'main_module', $storeId);
            $result[] = array(
                'code' => $sku,
                'version' => $version,
                'license' => $licenseNo,
                'main_module' => $main_module
            );
        }

        return $result;
    }
}