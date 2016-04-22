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

            $signature = $params['magentoInstanceSignature'];
            $key = $params['magentoInstancePublicKey'];

            unset($params['magentoInstancePublicKey']);
            unset($params['magentoInstanceSignature']);

            /** @var Local_Manadev_Model_Key $localKeyModel */
            $localKeyModel = Mage::getSingleton('local_manadev/key');
            /** @var Mana_Core_Model_Key $coreKeyModel */
            $coreKeyModel = Mage::getSingleton('mana_core/key');

            $requiredData = array(
                'magentoInstanceId',
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

            if($localKeyModel->verifySignatureFromAvailableKeys($coreKeyModel->dataToString($params), $signature, $key)) {
                if(Mage::getResourceModel('local_manadev/license_request')->ipHasExceededRequestLimit($params['remoteIp'])) {
                    throw new Exception("Maximum number of request reached! Please try again after 1 hour.");
                }

                /** @var Local_Manadev_Model_License_Request $requestModel */
                $requestModel = Mage::getModel('local_manadev/license_request')->load($params['magentoInstanceId'], 'magento_id');
                $willSave = false;
                $modulesInDb = $requestModel->getModules();
                $modulesInRequest = $params['installedModules'];
                ksort($modulesInDb);
                ksort($modulesInRequest);
                $extensionsInDb = $requestModel->getExtensions();
                $extensionsInRequest = $params['installedManadevExtensions'];
                array_multisort($extensionsInDb);
                array_multisort($extensionsInRequest);
                $storesInDb = $requestModel->getStores();
                $storesInRequest = $params['stores'];
                array_multisort($storesInDb);
                array_multisort($storesInRequest);

                if(!$requestModel->getId()) {
                    // Magento ID is not recognized. Save as new record.
                    $willSave = true;
                    $requestModel->setData('magento_id', $params['magentoInstanceId']);
                } else {
                    // Magento ID detected. Save only if there are any changes.
                    $match = $requestModel->getData('admin_url') == $params['adminPanelUrl'];
                    $match = $match && $requestModel->getData('remote_ip') == $params['remoteIp'];
                    $match = $match && $requestModel->getData('base_dir') == $params['baseDir'];
                    $match = $match && $requestModel->getData('magento_version') == $params['magentoVersion'];
                    $match = $match && json_encode($modulesInDb) == json_encode($modulesInRequest);
                    $match = $match && json_encode($extensionsInDb) == json_encode($extensionsInRequest);
                    $match = $match && json_encode($storesInDb) == json_encode($storesInRequest);

                    if(!$match) {
                        $willSave = true;
                    }
                }

                if($willSave) {
                    $requestModel = Mage::getModel('local_manadev/license_request');
                    $requestModel
                        ->setData('magento_id', $params['magentoInstanceId'])
                        ->setData('admin_url', $params['adminPanelUrl'])
                        ->setData('remote_ip', $params['remoteIp'])
                        ->setData('base_dir', $params['baseDir'])
                        ->setData('magento_version', $params['magentoVersion']);

                    $requestModel->setModules($modulesInRequest);
                    $requestModel->setExtensions($params['installedManadevExtensions']);
                    $requestModel->setStores($params['stores']);
                    try {
                        $requestModel->save();
                    } catch (Exception $e) {
                        throw new Exception("Something is wrong with the data provided.");
                    }
                }

                $result = array(
                    'latestManadevExtensionVersions' => $this->_getLatestVersionOfExtensions($params['installedManadevExtensions']),
                );

                echo $coreKeyModel->ssl_encrypt($coreKeyModel->dataToStringSerialize($result), 'public', $localKeyModel->getPublicKeyResource($key));
                exit();
            } else {
                throw new Exception("Key/Signature pair did not match");
            }
        } catch (Exception $e) {
            $protocol = "HTTP/1.0";
            if ( "HTTP/1.1" == $_SERVER["SERVER_PROTOCOL"] ) {
                $protocol = "HTTP/1.1";
            }
            header("$protocol 503 Service Unavailable", true, 503);
            header("Status: 503 Service Unavailable", true, 503);
            echo $e->getMessage();
            Mage::log($e, Zend_Log::DEBUG, 'manadev-exception.log', true);
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