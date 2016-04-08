<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Resource_License_Instance extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct() {
        $this->_init('local_manadev/license_instance', 'id');
    }

    /**
     * @param Local_Manadev_Model_License_Instance|Mage_Core_Model_Abstract $object
     * @return $this
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object) {
        /** @var Local_Manadev_Resource_License_Module_Collection $moduleCollection */
        $moduleCollection = Mage::getResourceModel('local_manadev/license_module_collection')->addFieldToFilter('instance_id', $object->getId());
        /** @var Local_Manadev_Resource_License_Extension_Collection $extensionCollection */
        $extensionCollection = Mage::getResourceModel('local_manadev/license_extension_collection')->addFieldToFilter('instance_id', $object->getId());

        $modules = array();
        $extensions = array();

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

        $object->setModules($modules);
        $object->setExtensions($extensions);

        return $this;
    }

    /**
     * @param Local_Manadev_Model_License_Instance|Mage_Core_Model_Abstract $object
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
                'instance_id' => $object->getId(),
            );
            $moduleCodes[] = "'{$code}'";
        }
        $where = new Zend_Db_Expr("module NOT IN(". implode(',', $moduleCodes) .") AND instance_id = " . $object->getId());

        $this->_getWriteAdapter()->delete($moduleResource->getMainTable(), $where);
        $this->_getWriteAdapter()->insertOnDuplicate($moduleResource->getMainTable(), $modules, array('version'));

        /** @var Local_Manadev_Resource_License_Extension $extensionResource */
        $extensionResource = Mage::getResourceModel('local_manadev/license_extension');
        $extensions = $object->getExtensions();
        $licenseVerificationNos = array();
        foreach($extensions as $x => $row) {
            $extensions[$x]['instance_id'] = $object->getId();
            $licenseVerificationNos[] = "'".$row['license_verification_no']."'";
        }
        $where = new Zend_Db_Expr("license_verification_no NOT IN(" . implode(',', $licenseVerificationNos) . ") AND instance_id = " . $object->getId());

        $this->_getWriteAdapter()->delete($extensionResource->getMainTable(), $where);
        $this->_getWriteAdapter()->insertOnDuplicate($extensionResource->getMainTable(), $extensions, array('version'));

        return $this;
    }


    protected function _prepareDataForSave(Mage_Core_Model_Abstract $object) {
        $currentTime = Varien_Date::now();
        if ((!$object->getId() || $object->isObjectNew()) && !$object->getCreatedAt()) {
            $object->setCreatedAt($currentTime);
        }
        $object->setUpdatedAt($currentTime);
        $data = parent::_prepareDataForSave($object);

        return $data;
    }
}