<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_ExtensionController extends Mage_Core_Controller_Front_Action
{
    public function updateAction(){
        try {
            $params = $this->getRequest()->getParams();

            if(!$params) {
                throw new Exception("Invalid request");
            }

            $signature = isset($params['magentoInstanceSignature']) ? $params['magentoInstanceSignature'] : false;
            $key = isset($params['magentoInstancePublicKey']) ? $params['magentoInstancePublicKey'] : false;

            unset($params['magentoInstancePublicKey']);
            unset($params['magentoInstanceSignature']);

            /** @var Local_Manadev_Model_Key $localKeyModel */
            $localKeyModel = Mage::getSingleton('local_manadev/key');
            /** @var Mana_Core_Model_Key $coreKeyModel */
            $coreKeyModel = Mage::getSingleton('mana_core/key');

            $requiredData = array(
                'installedManadevExtensions',
                'installedModules',
                'adminPanelUrl',
                'stores',
                'remoteIp',
                'baseDir',
                'magentoVersion',
            );
            foreach($requiredData as $requiredField) {
                if(!array_key_exists($requiredField, $params)) {
                    throw new Exception("Some required data are missing.");
                }
            }

            if($signature && !$localKeyModel->verifySignatureFromAvailableKeys($coreKeyModel->dataToString($params), $signature, $key)) {
                throw new Exception("Key/Signature pair did not match");
            }

            if(Mage::getResourceModel('local_manadev/license_request')->ipHasExceededRequestLimit($params['remoteIp'])) {
                throw new Exception("Maximum number of request reached! Please try again after 1 hour.");
            }

            if(isset($params['magentoInstanceId'])) {
                $magento_id = $params['magentoInstanceId'];
                $newMagentoId = false;
            } else {
                $magento_id = false;
                $newMagentoId = true;
            }

            $modulesInRequest = $params['installedModules'];
            ksort($modulesInRequest);

            $extensionsInRequest = $params['installedManadevExtensions'];
            array_multisort($extensionsInRequest);

            $storesInRequest = $params['stores'];
            array_multisort($storesInRequest);


            $requestModel = Mage::getModel('local_manadev/license_request');
            if($magento_id) {
                $requestModel->load($magento_id, 'magento_id');
            } else {
                $requestModel
                    ->setData('magento_id', $magento_id)
                    ->setData('admin_url', $params['adminPanelUrl'])
                    ->setData('remote_ip', $params['remoteIp'])
                    ->setData('base_dir', $params['baseDir'])
                    ->setData('magento_version', $params['magentoVersion']);

                $requestModel->setModules($modulesInRequest);
                $requestModel->setExtensions($params['installedManadevExtensions']);
                $requestModel->setStores($params['stores']);
                $magento_id = $requestModel->getResource()->generateMagentoId($requestModel);
                $requestModel->setData('magento_id', $magento_id);
            }


            $willSave = false;
            $checkDup = Mage::getModel('local_manadev/license_request')->load($magento_id, 'magento_id');
            if(!$checkDup->getId()) {
                // Magento ID is not recognized. Save as new record.
                $willSave = true;
            } else {
                $modulesInDb = $requestModel->getModules();
                ksort($modulesInDb);
                $extensionsInDb = $requestModel->getExtensions();
                array_multisort($extensionsInDb);
                $storesInDb = $requestModel->getStores();
                array_multisort($storesInDb);

                $itemsToCheck = array(
                    'admin_url' => $requestModel->getData('admin_url') == $params['adminPanelUrl'],
                    'remote_ip' => $requestModel->getData('remote_ip') == $params['remoteIp'],
                    'basedir' => $requestModel->getData('base_dir') == $params['baseDir'],
                    'magento_version' => $requestModel->getData('magento_version') == $params['magentoVersion'],
                    'modules' => json_encode($modulesInDb) == json_encode($modulesInRequest),
                    'extensions' => json_encode($extensionsInDb) == json_encode($extensionsInRequest),
                    'stores' => json_encode($storesInDb) == json_encode($storesInRequest),
                );

                $perfectMatch = true;
                foreach($itemsToCheck as $data => $match) {
                    if(!$match) {
                        $perfectMatch = false;
                        break;
                    }
                }

                if(!$perfectMatch) {
                    // Something changed, save it.
                    $willSave = true;
                } else {
                    // Same data. Do not create a new record. Update last_checked instead.
                    $requestModel->load($checkDup->getId());
                    $requestModel->setData('last_checked', Varien_Date::now())->save();
                }
            }

            if($willSave) {
                try {
                    $requestModel->save();
                } catch (Exception $e) {
                    throw new Exception("Something is wrong with the data provided.");
                }
            }

            $result = array(
                'latestManadevExtensionVersions' => $this->_getLatestVersionOfExtensions($params['installedManadevExtensions']),
            );
            if($newMagentoId) {
                $result['generatedMagentoId'] = $magento_id;
            }

            if($key === false) {
                throw new Exception("");
            }
            echo $coreKeyModel->ssl_encrypt($coreKeyModel->dataToStringSerialize($result), 'public', $localKeyModel->getPublicKeyResource($key));
            exit();
        } catch (Exception $e) {
            $protocol = "HTTP/1.0";
            if ( "HTTP/1.1" == $_SERVER["SERVER_PROTOCOL"] ) {
                $protocol = "HTTP/1.1";
            }
            header("$protocol 503 Service Unavailable", true, 503);
            header("Status: 503 Service Unavailable", true, 503);
            echo $e->getMessage();
            Mage::log($e, Zend_Log::DEBUG, 'manadev-exception.log');
            exit();
        }

    }

    protected function _getLatestVersionOfExtensions($installedManadevExtensions) {
        $result = array();
        foreach($installedManadevExtensions as $extension) {
            $itemModel = Mage::getModel('downloadable/link_purchased_item')->load($extension['license_verification_no'], 'm_license_verification_no');
            $licenseNo = $itemModel->getData('m_license_no');
            if(is_null($licenseNo)) {
                $licenseNo = '';
                $version = $extension['version'];
            } else {
                if(Mage::helper('local_manadev')->createNewZipFileWithLicense($itemModel)) {
                    $itemModel->save();
                }
                $version = $this->_getLocalKeyModel()->getVersionFromZipFile($itemModel->getLinkFile());
            }

            $sku = $extension['code'];
            $result[] = array(
                'code' => $sku,
                'version' => $version,
                'license' => $licenseNo,
            );
        }

        return $result;
    }

    /**
     * @return Local_Manadev_Model_Key
     */
    protected function _getLocalKeyModel() {
        return Mage::getModel('local_manadev/key');
    }
}