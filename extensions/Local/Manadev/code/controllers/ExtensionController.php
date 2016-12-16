<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_ExtensionController extends Mage_Core_Controller_Front_Action
{
    public function listAction() {
        try {
            if (!($apiKey = $this->getRequest()->getParam('api_key'))) {
                throw new Exception('No API key');
            }

            if (!($allowedApiKeys = Mage::getStoreConfig('local_manadev/demo/allowed_api_keys'))) {
                throw new Exception('No API keys allowed');
            }

            $allowedApiKeys = array_filter(explode(',', $allowedApiKeys));
            if (!in_array($apiKey, $allowedApiKeys)) {
                throw new Exception("API Key '$apiKey' not allowed.");
            }

            $res = Mage::getSingleton('core/resource');
            $db = $res->getConnection('read');
            $select = $db->select()
                ->from(array('e' => 'catalog_product_flat_' . Mage::app()->getStore('default')->getId()),
                    array('entity_id', 'demo_description', 'url_key', 'name'))
                ->joinInner(array('price' => 'catalog_product_index_price'), "`price`.`entity_id` = `e`.`entity_id` AND " .
                    "`price`.`customer_group_id` = 0", 'final_price');

            if ($productId = $this->getRequest()->getParam('product')) {
                $select->where("`e`.`entity_id` = ?", $productId);
            }
            else {
                if (!($platform = $this->getRequest()->getParam('platform'))) {
                    throw new Exception('No platform');
                }

                /* @var Local_Manadev_Model_Platform $platformSource */
                $platformSource = Mage::getModel('local_manadev/platform');
                $platforms = $platformSource->getOptionArray();
                if (!isset($platforms[$platform])) {
                    throw new Exception('Unknown platform');
                }

                $platformAttributeId = $db->fetchOne($db->select()
                    ->from(array('a' => 'eav_attribute'), 'attribute_id')
                    ->joinInner(array('t' => 'eav_entity_type'), $db->quoteInto(
                        "`t`.`entity_type_id` = `a`.`entity_type_id` AND `t`.`entity_type_code` = ?", 'catalog_product'), null)
                    ->where("`a`.`attribute_code` = ?", 'platform'));

                $select->joinInner(array('platform' => 'catalog_product_entity_int'),
                    "`platform`.`entity_id` = `e`.`entity_id` AND ".
                    $db->quoteInto("`platform`.`store_id` = ?", 0) . " AND " .
                    $db->quoteInto("`platform`.`value` = ?", $platform) . " AND " .
                    $db->quoteInto("`platform`.`attribute_id` = ?", $platformAttributeId), null);
            }

            $result = array();
            foreach ($db->fetchAll($select) as $product) {
                /* @var Mage_Catalog_Model_Product $product */
                $result[$product['entity_id']] = array(
                    'name' => $product['name'],
                    'price' => $product['final_price'],
                    'url' => Mage::getUrl(null, array('_direct' => $product['url_key'] .
                        Mage::getStoreConfig('catalog/seo/product_url_suffix'))),
                    'description' => $product['demo_description'],
                );
            }

            echo json_encode($result);
            exit();
        }
        catch (Exception $e) {
            $protocol = "HTTP/1.0";
            if ( "HTTP/1.1" == $_SERVER["SERVER_PROTOCOL"] ) {
                $protocol = "HTTP/1.1";
            }
            header("$protocol 503 Service Unavailable", true, 503);
            header("Status: 503 Service Unavailable", true, 503);
            echo '503 Service Unavailable';

            Mage::log($e->getMessage(), Zend_Log::DEBUG, 'manadev-demo_api.log');
            Mage::log($e->getTraceAsString(), Zend_Log::DEBUG, 'manadev-demo_api.log');
            Mage::log(json_encode($this->getRequest()->getParams()), Zend_Log::DEBUG, 'manadev-demo_api.log');

            exit();
        }
    }

    protected function joinAttribute($attributeCode) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));

        /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        $attribute = $core->collectionFind($this->getAttributes(), 'attribute_code', $attributeCode);

        /* @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = $this->_productCollection->getConnection();
        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');

        $alias = 'mp_'.$attributeCode;
        $from = $this->_productCollection->getSelect()->getPart(Varien_Db_Select::FROM);
        if (!isset($from[$alias])) {
            $this->_productCollection->getSelect()->joinLeft(
                array($alias => $attribute->getBackendTable()),
                implode(' AND ', array(
                    "`$alias`.`entity_id` = `e`.`entity_id`",
                    $db->quoteInto("`$alias`.`attribute_id` = ?", $attribute->getId()),
                    "`$alias`.`store_id` = 0",
                )),
                null
            );
        }

        return "`$alias`.`value`";
    }


    public function updateAction(){
        try {
            $params = $this->getRequest()->getParams();
            Mage::log('', Zend_Log::DEBUG, 'license_request.log');
            Mage::log($_SERVER['REMOTE_ADDR'] . ' request: ' . json_encode($params), Zend_Log::DEBUG, 'license_request.log');

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
                'extensions',
                'modules',
                'admin',
                'stores',
                'dir',
                'version',
            );
            foreach($requiredData as $requiredField) {
                if(!array_key_exists($requiredField, $params)) {
                    throw new Exception("$requiredField is missing.");
                }
            }

            $jsonData = array('extensions', 'modules', 'stores');
            foreach ($jsonData as $field) {
                if (($params[$field] = json_decode($params[$field], true)) === null) {
                    throw new Exception("$field is not valid JSON");
                }
            }

            if(isset($params['id'])) {
                $magento_id = $params['id'];
                $newMagentoId = false;
            } else {
                $magento_id = false;
                $newMagentoId = true;
            }

            $this->_sortItems($params['modules'], $params['extensions'], $params['stores']);

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
                $extensionsInDb = $lastRequest->getExtensions();
                $storesInDb = $lastRequest->getStores();
                $this->_sortItems($modulesInDb, $extensionsInDb, $storesInDb);

                $itemsToCheck = array(
                    'admin_url' => $lastRequest->getData('admin_url') == $params['admin'],
                    'remote_ip' => $lastRequest->getData('remote_ip') == $_SERVER['REMOTE_ADDR'],
                    'basedir' => $lastRequest->getData('base_dir') == $params['dir'],
                    'magento_version' => $lastRequest->getData('magento_version') == $params['version'],
                    'modules' => json_encode($modulesInDb) == json_encode($params['modules']),
                    'extensions' => json_encode($extensionsInDb) == json_encode($params['extensions']),
                    'stores' => json_encode($storesInDb) == json_encode($params['stores']),
                );

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
                $magento_id = $this->saveRequestToDb($params);
            }

            $result = array(
                'versions' => $this->_getLatestVersionOfExtensions($params['extensions']),
            );
            if($newMagentoId ) {
                $result['id'] = $magento_id;
                Mage::log($_SERVER['REMOTE_ADDR'] . ' Magento ID (newly assigned): ' . $magento_id .
                    ', '. $params['admin'], Zend_Log::DEBUG, 'license_request.log');
            }
            else {
                Mage::log($_SERVER['REMOTE_ADDR'] . ' Magento ID: ' . $magento_id .
                    ', ' . $params['admin'], Zend_Log::DEBUG, 'license_request.log');
            }

            Mage::log($_SERVER['REMOTE_ADDR'] . ' response: ' . json_encode($result), Zend_Log::DEBUG, 'license_request.log');
            $s = json_encode($result);
            $r=''; for ($i=0;$i<strlen($s);$i++) $r.=($i+1==strlen($s)&&$i%2==0)?$s[$i]:($i%2==0?$s[$i+1]:$s[$i-1]);
            echo base64_encode(implode(array_map(function($r){return chr(ord($r)+1);},str_split($r))));
            exit();
        } catch (Exception $e) {
            $protocol = "HTTP/1.0";
            if ( "HTTP/1.1" == $_SERVER["SERVER_PROTOCOL"] ) {
                $protocol = "HTTP/1.1";
            }
            header("$protocol 503 Service Unavailable", true, 503);
            header("Status: 503 Service Unavailable", true, 503);
            echo '503 Service Unavailable';

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

        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton(strtolower('Core/Resource'));
        $db = $res->getConnection('read');
        $notAvailable = Local_Manadev_Model_Download_Status::M_LINK_STATUS_NOT_AVAILABLE;

        foreach($installedManadevExtensions as $extension) {
            $price = $db->fetchOne($db->select()
                ->from(array('e' => $res->getTableName('catalog/product')), null)
                ->joinInner(array('price' => 'catalog_product_index_price'), "`price`.`entity_id` = `e`.`entity_id` AND " .
                    "`price`.`customer_group_id` = 0", 'final_price')
                ->where("`e`.`sku` = ?", $extension['code'])
            );
            $isFree = $price !== false && !($price > 0);

            $itemModel = Mage::getModel('downloadable/link_purchased_item')->load($extension['license_verification_no'], 'm_license_verification_no');

            if (!$itemModel->getId()) {
                $licenseNo = '';
                if (!$isFree) {
                    Mage::log($_SERVER['REMOTE_ADDR'] . ' Extension disabled: ' . $extension['code'] .
                        '. Reason: License record not found by verification no: ' . $extension['license_verification_no'],
                        Zend_Log::DEBUG, 'license_request.log');
                }
            }
            elseif ($itemModel->getData("status") == $notAvailable) {
                $licenseNo = '';

                if (!$isFree) {
                    Mage::log($_SERVER['REMOTE_ADDR'] . ' Extension disabled: ' . $extension['code'] .
                        '. Reason: License status is Not Available, verification no: ' . $extension['license_verification_no'],
                        Zend_Log::DEBUG, 'license_request.log');
                }
            }
            else {
                $licenseNo = $itemModel->getData('m_license_no');
            }

            if ($licenseNo) {
                $code = $db->fetchOne($db->select()
                    ->from(array('p' => $res->getTableName('catalog/product')), 'p.sku')
                    ->joinInner(array('oi' => $res->getTableName('sales/order_item')), "`oi`.`product_id` = `p`.`entity_id`", null)
                    ->where("`oi`.`item_id` = ?", $itemModel->getData('order_item_id')));

                if ($code != $extension['code']) {
                    $licenseNo = '';
                    if (!$isFree) {
                        if ($code) {
                            Mage::log($_SERVER['REMOTE_ADDR'] . ' Extension disabled: ' . $extension['code'] .
                                '. Reason: License issues for another extension: ' . $code,
                                Zend_Log::DEBUG, 'license_request.log');
                        }
                        else {
                            Mage::log($_SERVER['REMOTE_ADDR'] . ' Extension disabled: ' . $extension['code'] .
                                '. Reason: extension not found by SKU.',
                                Zend_Log::DEBUG, 'license_request.log');
                        }
                    }
                }
            }

            if ($licenseNo) {
                if(Mage::helper('local_manadev')->createNewZipFileWithLicense($itemModel)) {
                    $itemModel->save();
                }
                $version = $this->_getLocalKeyModel()->getVersionFromZipFile($itemModel->getLinkFile());

                $result[] = array(
                    'code' => $extension['code'],
                    'version' => $version,
                    'license' => $licenseNo,
                );
            }
            else {
                $result[] = array(
                    'code' => $extension['code'],
                    'version' => '',
                    'license' => $isFree ? 'free' : '',
                );
            }
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
            ->setData('admin_url', $params['admin'])
            ->setData('remote_ip', $_SERVER['REMOTE_ADDR'])
            ->setData('base_dir', $params['dir'])
            ->setData('magento_version', $params['version'])
            ->setData('last_checked', Varien_Date::now())
            ->setData('changed_data', $changedData);
        $requestModel->setModules($params['modules']);
        $requestModel->setExtensions($params['extensions']);
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

    protected function _sortItems(&$modules, &$extensions, &$stores) {
        sort($modules);
        array_multisort($extensions);
        array_multisort($stores);
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