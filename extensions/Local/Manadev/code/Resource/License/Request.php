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
        $this->_getWriteAdapter()->insertOnDuplicate($moduleResource->getMainTable(), $modules, array('version'));

        /** @var Local_Manadev_Resource_License_Extension $extensionResource */
        $extensionResource = Mage::getResourceModel('local_manadev/license_extension');
        $extensions = $object->getExtensions();
        $licenseVerificationNos = array();
        foreach($extensions as $x => $row) {
            $extensions[$x]['request_id'] = $object->getId();
            $licenseVerificationNos[] = "'".$row['license_verification_no']."'";
        }
        $whereStr = "request_id = " . $object->getId();
        if(count($licenseVerificationNos)) {
            $whereStr .= " AND license_verification_no NOT IN(" . implode(',', $licenseVerificationNos) . ")";
        }

        $where = new Zend_Db_Expr($whereStr);

        $this->_getWriteAdapter()->delete($extensionResource->getMainTable(), $where);
        $this->_getWriteAdapter()->insertOnDuplicate($extensionResource->getMainTable(), $extensions, array('version'));

        /** @var Local_Manadev_Resource_License_Store $extensionResource */
        $storeResource = Mage::getResourceModel('local_manadev/license_store');
        $stores = $object->getStores();
        Mage::log($stores, Zend_Log::DEBUG, 'manadev-stores.log', true);
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
        $this->_getWriteAdapter()->insertOnDuplicate($storeResource->getMainTable(), $stores, array('frontend_url', 'theme'));

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
}