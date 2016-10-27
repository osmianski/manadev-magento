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

            /** @var Local_Manadev_Resource_License_Request_Log $logResource */
            $logResource = Mage::getResourceModel('local_manadev/license_request_log');
            $logResource->deleteOldRequestLogs();
            if($logResource->hasExceededRequestLimit()) {
                throw new Exception("Maximum number of request reached! Please try again after 1 hour.");
            }
            $logResource->logRequest();

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

            if(isset($params['magentoInstanceId'])) {
                $magento_id = $params['magentoInstanceId'];
                $newMagentoId = false;
            } else {
                $magento_id = false;
                $newMagentoId = true;
            }

            $this->_sortItems($params);

            /** @var Local_Manadev_Resource_License_Request_Collection $collection */
            $collection = Mage::getResourceModel('local_manadev/license_request_collection');
            $lastRequest = $collection
                ->addFieldToFilter("magento_id", $magento_id)
                ->addOrder("created_at", 'desc')
                ->getFirstItem();

            if($lastRequest->getId()) {
                // Reload model to get extension, modules, and store data.
                $lastRequest->load($lastRequest->getId());
                $modulesInDb = $lastRequest->getModules();
                ksort($modulesInDb);
                $extensionsInDb = $lastRequest->getExtensions();
                array_multisort($extensionsInDb);
                $storesInDb = $lastRequest->getStores();
                array_multisort($storesInDb);

                $itemsToCheck = array(
                    'admin_url' => $lastRequest->getData('admin_url') == $params['adminPanelUrl'],
                    'remote_ip' => $lastRequest->getData('remote_ip') == $_SERVER['REMOTE_ADDR'],
                    'basedir' => $lastRequest->getData('base_dir') == $params['baseDir'],
                    'magento_version' => $lastRequest->getData('magento_version') == $params['magentoVersion'],
                    'modules' => json_encode($modulesInDb) == json_encode($params['installedModules']),
                    'extensions' => json_encode($extensionsInDb) == json_encode($params['installedManadevExtensions']),
                    'stores' => json_encode($storesInDb) == json_encode($params['stores']),
                );

                Mage::log($lastRequest->getId(), Zend_Log::DEBUG, 'debug.log');
                Mage::log($lastRequest->getData('remote_ip'), Zend_Log::DEBUG, 'debug.log');
                Mage::log($_SERVER['REMOTE_ADDR'], Zend_Log::DEBUG, 'debug.log');

                $perfectMatch = true;
                $changedData = array();
                foreach($itemsToCheck as $data => $match) {
                    if(!$match) {
                        $changedData[] = $data;
                        $perfectMatch = false;
                    }
                }

                if(!$perfectMatch) {
                    // Something changed, save it.
                    $this->saveRequestToDb($params, $changedData);
                } else {
                    // Same data. Do not create a new record. Update last_checked instead.
                    Mage::getModel('local_manadev/license_request')
                        ->load($lastRequest->getId())
                        ->setData('last_checked', Varien_Date::now())
                        ->save();
                }
            } else {
                // First request received from this Magento ID, save it.
                $magento_id = $this->saveRequestToDb($params)->getData('magento_id');
            }

            $result = array(
                'latestManadevExtensionVersions' => $this->_getLatestVersionOfExtensions($params['installedManadevExtensions']),
            );
            if($newMagentoId ) {
                $result['generatedMagentoId'] = $magento_id;
            }

            echo $coreKeyModel->dataToStringSerialize($result);
            exit();
        } catch (Exception $e) {
            $protocol = "HTTP/1.0";
            if ( "HTTP/1.1" == $_SERVER["SERVER_PROTOCOL"] ) {
                $protocol = "HTTP/1.1";
            }
            header("$protocol 503 Service Unavailable", true, 503);
            header("Status: 503 Service Unavailable", true, 503);
            echo 'Invalid Magento instance data.';

            Mage::log($e->getMessage(), Zend_Log::DEBUG, 'manadev-license-exception.log');
            Mage::log($e->getTraceAsString(), Zend_Log::DEBUG, 'manadev-license-exception.log');

            if(isset($params)) {
                Mage::log(json_encode($params), Zend_Log::DEBUG, 'manadev-license-exception.log');
            }

            exit();
        }

    }

    protected function _getLatestVersionOfExtensions($installedManadevExtensions) {
        $result = array();
        foreach($installedManadevExtensions as $extension) {
            $itemModel = Mage::getModel('downloadable/link_purchased_item')->load($extension['license_verification_no'], 'm_license_verification_no');
            $licenseNo = $itemModel->getData('m_license_no');
            if(
                is_null($licenseNo) ||
                $itemModel->getData("status") == Local_Manadev_Model_Download_Status::M_LINK_STATUS_NOT_AVAILABLE
            ) {
                // Do not include in the $result array so it gets disabled in client installation.
                continue;
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

    protected function saveRequestToDb($params, $changedData = array()) {
        $changedData = count($changedData) ? $this->_humanizeChangedData($changedData): null;

        /** @var Local_Manadev_Model_License_Request $requestModel */
        $requestModel = Mage::getModel('local_manadev/license_request');

        $requestModel
            ->setData('admin_url', $params['adminPanelUrl'])
            ->setData('remote_ip', $_SERVER['REMOTE_ADDR'])
            ->setData('base_dir', $params['baseDir'])
            ->setData('magento_version', $params['magentoVersion'])
            ->setData('last_checked', Varien_Date::now())
            ->setData('changed_data', $changedData);
        $requestModel->setModules($params['installedModules']);
        $requestModel->setExtensions($params['installedManadevExtensions']);
        $requestModel->setStores($params['stores']);

        if (isset($params['magentoInstanceId'])) {
            $magento_id = $params['magentoInstanceId'];
        }
        else {
            $magento_id = $requestModel->getResource()->generateMagentoId($requestModel);
        }
        $requestModel->setData('magento_id', $magento_id);

        $requestModel->save();
        return $magento_id;
    }

    protected function _sortItems(&$params) {
        $modulesInRequest = $params['installedModules'];
        ksort($modulesInRequest);
        $params['installedModules'] = $modulesInRequest;

        $extensionsInRequest = $params['installedManadevExtensions'];
        array_multisort($extensionsInRequest);
        $params['installedManadevExtensions'] = $extensionsInRequest;

        $storesInRequest = $params['stores'];
        array_multisort($storesInRequest);
        $params['stores'] = $storesInRequest;
    }

    protected function _humanizeChangedData($changedData) {
        $values = array(
            'admin_url' => "Admin Panel URL",
            'remote_ip' => "Server IP Address",
            'basedir' => "Installation Base Directory",
            'magento_version' => "Magento Version",
            'modules' => "Modules",
            'extensions' => "Installed Extensions",
            'stores' => "Store Information",
        );

        $result = array();
        foreach($changedData as $key) {
            $result[] = $values[$key];
        }
        return implode(", ", $result);
    }
}