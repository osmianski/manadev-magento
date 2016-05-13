<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Resource_License_Request extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct() {
        $this->_init('local_manadev/license_request', 'id');
    }

    public function ipHasExceededRequestLimit($ip_address) {
        $select = $this->_getWriteAdapter()->select();
        $select
            ->from($this->getMainTable())
            ->where("remote_ip = ?", $ip_address)
            ->order("created_at DESC")
            ->limit(1, 14);
        $data = $this->_getReadAdapter()->fetchRow($select);

        if($data) {
            $last15Rec = new DateTime($data['created_at']);
            $varienNow = Varien_Date::now();
            $now = new DateTime($varienNow);
            $diff = $last15Rec->diff($now);

            if($diff->format('%h') == 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Local_Manadev_Model_License_Request $requestModel
     * @return string
     */
    public function generateMagentoId($requestModel) {
        $prefix = "M";
        $salt = "r2RKKMBx6ZUmPQQ8";

        $x = json_encode(ksort($requestModel->getModules())) . '|' .
            json_encode(array_multisort($requestModel->getStores())) . '|' .
            $requestModel->getData('admin_url') . '|' .
            $requestModel->getData('remote_ip') . '|' .
            $requestModel->getData('base_dir') . '|' .
            $salt
        ;

        $magento_id = $this->_getKeyModel()->shaToLicenseNo(sha1($x));
        $magento_id = $prefix .
            substr($magento_id, 0, 5) . '-' .
            substr($magento_id, 5, 6) . '-' .
            substr($magento_id, 11, 5) . '-' .
            substr($magento_id, 16, 6) . '-' .
            substr($magento_id, 22, 5);

        return $magento_id;

    }

    /**
     * @param Local_Manadev_Model_License_Request|Mage_Core_Model_Abstract $object
     * @return $this
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object) {
        /** @var Local_Manadev_Resource_License_Module_Collection $moduleCollection */
        $moduleCollection = Mage::getResourceModel('local_manadev/license_module_collection')->addFieldToFilter('request_id', $object->getId());
        /** @var Local_Manadev_Resource_License_Extension_Collection $extensionCollection */
        $extensionCollection = Mage::getResourceModel('local_manadev/license_extension_collection')->addFieldToFilter('request_id', $object->getId());
        /** @var Local_Manadev_Resource_License_Store_Collection $storesCollection */
        $storesCollection = Mage::getResourceModel('local_manadev/license_store_collection')->addFieldToFilter('request_id', $object->getId());

        $modules = array();
        $extensions = array();
        $stores = array();

        foreach($moduleCollection->getItems() as $row) {
            $modules[$row['module']] = $row['version'];
        }

        foreach($extensionCollection->getItems() as $row) {
            $extensions[] = array(
                'code' => $row['code'],
                'version' => $row['version'],
                'license_verification_no' => $row['license_verification_no'],
            );
        }

        foreach($storesCollection->getItems() as $row) {
            $stores[] = array(
                'storeId' => $row['store_id'],
                'url' => $row['frontend_url'],
                'theme' => $row['theme'],
            );
        }

        $object->setModules($modules);
        $object->setExtensions($extensions);
        $object->setStores($stores);

        return $this;
    }

    /**
     * @param Local_Manadev_Model_License_Request|Mage_Core_Model_Abstract $object
     * @return $this
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object) {
        /** @var Local_Manadev_Resource_License_Module $moduleResource */
        $moduleResource = Mage::getResourceModel('local_manadev/license_module');
        $modules = array();
        $moduleCodes = array();
        foreach($object->getModules() as $code => $version) {
            $modules[] = array(
                'module' => $code,
                'version' => $version,
                'request_id' => $object->getId(),
            );
            $moduleCodes[] = "'{$code}'";
        }
        $whereStr = "request_id = " . $object->getId();

        if(count($moduleCodes)) {
            $whereStr .= " AND module NOT IN(". implode(',', $moduleCodes) .")";
        }
        $where = new Zend_Db_Expr($whereStr);

        $this->_getWriteAdapter()->delete($moduleResource->getMainTable(), $where);
        if($modules) {
            $this->_getWriteAdapter()->insertOnDuplicate($moduleResource->getMainTable(), $modules, array('version'));
        }

        /** @var Local_Manadev_Resource_License_Extension $extensionResource */
        $extensionResource = Mage::getResourceModel('local_manadev/license_extension');
        $extensions = $object->getExtensions();

        $codes = array();
        $licenseVerificationNos = array();
        foreach($extensions as $x => $row) {
            $extensions[$x]['request_id'] = $object->getId();
            $codes[] = "'".$row['code']."'";
            $licenseVerificationNos[] = $row['license_verification_no'];
        }
        $whereStr = "request_id = " . $object->getId();
        if(count($codes)) {
            $whereStr .= " AND code NOT IN(" . implode(',', $codes) . ")";
        }

        $where = new Zend_Db_Expr($whereStr);

        $this->_getWriteAdapter()->delete($extensionResource->getMainTable(), $where);
        if($extensions) {
            $this->_getWriteAdapter()->insertOnDuplicate($extensionResource->getMainTable(), $extensions, array('version'));
        }

        /** @var Local_Manadev_Resource_Downloadable_Item $purchasedItem */
        $purchasedItem = Mage::getResourceModel('downloadable/link_purchased_item');
        $purchasedItem->upgradeAggregateByLicenseVerificationNos($licenseVerificationNos);

        /** @var Local_Manadev_Resource_License_Store $extensionResource */
        $storeResource = Mage::getResourceModel('local_manadev/license_store');
        $stores = $object->getStores();
        $storeIds = array();
        foreach($stores as $x => $store) {
            $stores[$x]['request_id'] = $object->getId();
            $storeIds[] = $store['storeId'];
            $stores[$x]['store_id'] = $store['storeId'];
            unset($stores[$x]['storeId']);
            $stores[$x]['frontend_url'] = $store['url'];
            unset($stores[$x]['url']);
        }

        $whereStr = "request_id = " . $object->getId();

        if(count($storeIds)) {
            $whereStr .= " AND store_id NOT IN(" . implode(',', $storeIds) . ")";
        }

        $where = new Zend_Db_Expr($whereStr);

        $this->_getWriteAdapter()->delete($storeResource->getMainTable(), $where);
        if($stores) {
            $this->_getWriteAdapter()->insertOnDuplicate($storeResource->getMainTable(), $stores, array('frontend_url', 'theme'));
        }

        return $this;
    }


    /**
     * @param Local_Manadev_Model_License_Request|Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object) {
        $stores = $object->getStores();
        $urls = array();
        $themes = array();
        foreach($stores as $x => $store) {
            $urls[] = $store['url'];
            $themes[] = $store['theme'];
        }
        $object->setData('agg_frontend_urls', implode('|', array_unique($urls)));
        $object->setData('agg_themes', implode('|', array_unique($themes)));

        $moduleCodes = array();
        implode('|', array_keys($object->getModules()));
        foreach ($object->getModules() as $code => $version) {
            $moduleCodes[] = $code;
        }
        $object->setData('agg_modules', implode('|', $moduleCodes));

        return $this;
    }

    protected function _prepareDataForSave(Mage_Core_Model_Abstract $object) {
        $currentTime = Varien_Date::now();
        if ((!$object->getId() || $object->isObjectNew()) && !$object->getCreatedAt()) {
            $object->setCreatedAt($currentTime);
        }
        $data = parent::_prepareDataForSave($object);

        return $data;
    }

    protected function _getLoadSelect($field, $value, $object) {
        $field  = $this->_getReadAdapter()->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), $field));
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where($field . '=?', $value)
            ->limit(1)
            ->order("created_at desc");
        return $select;
    }

    /**
     * @return Local_Manadev_Model_Key
     */
    protected function _getKeyModel() {
        return Mage::getModel('local_manadev/key');
    }
}