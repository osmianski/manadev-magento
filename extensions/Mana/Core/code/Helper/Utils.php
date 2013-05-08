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

        foreach ($indexer->getProcessesCollection() as $process) {
            /* @var $process Mage_Index_Model_Process */
            $process->reindexAll();
        }
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
}