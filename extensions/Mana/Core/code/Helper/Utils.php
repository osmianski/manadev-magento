<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Core_Helper_Utils extends Mage_Core_Helper_Abstract {
    /**
     * @return Mana_Core_Helper_Utils
     */
    public function reindexAll() {
        /* @var $indexer Mage_Index_Model_Indexer */
        $indexer = Mage::getSingleton('index/indexer');

        Mage::dispatchEvent('shell_reindex_init_process');
        foreach ($indexer->getProcessesCollection() as $process) {
            /* @var $process Mage_Index_Model_Process */
            $process->reindexEverything();
            Mage::dispatchEvent($process->getIndexerCode() . '_shell_reindex_after');
        }
        Mage::dispatchEvent('shell_reindex_finalize_process');

        return $this;
    }

    public function reindex($code) {
        /* @var $indexer Mage_Index_Model_Indexer */
        $indexer = Mage::getSingleton('index/indexer');

        $indexer->getProcessByCode($code)->reindexAll();

        return $this;
    }

    /**
     * @param string $module
     * @return Mana_Core_Helper_Utils
     */
    public function disableModuleOutput($module) {
        /* @var $configData Mage_Core_Model_Config_Data */
        $configData = Mage::getModel('core/config_data');
        /* @noinspection PhpUndefinedMethodInspection */
        $configData
            ->setScope('default')
            ->setScopeId(0)
            ->setPath('advanced/modules_disable_output/'. $module)
            ->setValue(1)
            ->save();

        return $this;
    }

    /**
     * @return Mana_Core_Helper_Utils
     */
    public function clearDiskCache() {
        /* @var $fileHelper Mana_Core_Helper_Files */
        $fileHelper = Mage::helper('mana_core/files');
        $fileHelper->walkRecursively(Mage::getBaseDir('cache'), array($this, '_clearDiskCacheEntry'));

        return $this;
    }

    public function _clearDiskCacheEntry($filename, $isDir) {
        if (!$isDir) {
            unlink($filename);
        }

        return true;
    }

    public function setStoreConfig($path, $value, $scope = 'default', $scopeId = 0) {
        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');

        /* @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = $res->getConnection('write');

        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        $db->query($core->insert($db, $res->getTableName('core/config_data'), array(
            'scope' => "'$scope'",
            'scope_id' => $scopeId,
            'path' => "'$path'",
            'value' => "'$value'",
        )));

        return $this;
    }

    public function getStoreConfig($path) {
        $scope = 'default';
        $scopeId = 0;

        /* @var $collection Mage_Core_Model_Mysql4_Config_Data_Collection */
        $collection = Mage::getModel('core/config_data')->getCollection();

        $collection->getSelect()
            ->where('scope=?', $scope)
            ->where('scope_id=?', $scopeId)
            ->where('path=?', $path);

        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach ($collection as $result) {
            /* @noinspection PhpUndefinedMethodInspection */
            return $result->getValue();
        }

        return (string)Mage::getConfig()->getNode('default/'.$path);
    }

}