<?php
/**
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method Mage_Core_Model_Resource_Setup getInstaller()
 * @method Mana_Db_Model_Setup setInstaller(Mage_Core_Model_Resource_Setup $value)
 * @method string getModuleName()
 * @method Mana_Db_Model_Setup setModuleName(string $value)
 * @method string getVersion()
 * @method Mana_Db_Model_Setup setVersion(string $value)
 * @method string getSetupVersion()
 * @method Mana_Db_Model_Setup setSetupVersion(string $value)
 */
class Mana_Db_Model_Setup extends Varien_Object {
    public function run ($installer, $moduleName, $version) {
        /* @var $configHelper Mana_Db_Helper_Config */
        $configHelper = Mage::helper('mana_db/config');

        $installerKey = 'v'.$version;
        $setupVersion = $configHelper->getXml()->getNode()->modules->$moduleName->installer_versions->$installerKey;

        $this
            ->setInstaller($installer)
            ->setModuleName($moduleName)
            ->setVersion($version)
            ->setSetupVersion($setupVersion)
            ->_beforeRun()
            ->_run()
            ->_afterRun();
    }
    protected function _beforeRun() {
        $installer = $this->getInstaller();

        if (defined('COMPILER_INCLUDE_PATH')) {
            throw new Exception(Mage::helper('mana_core')->__(
                'This Magento installation contains pending database installation/upgrade scripts. Please turn off Magento compilation feature while installing/upgrading new modules in Admin Panel menu System->Tools->Compilation.'
            ));
        }

        if (method_exists($installer->getConnection(), 'allowDdlCache')) {
            $installer->getConnection()->allowDdlCache();
        }

        return $this;
    }
    protected function _run() {
        /* @var $configHelper Mana_Db_Helper_Config */
        $configHelper = Mage::helper('mana_db/config');

        $setupVersion = $configHelper->getSetup($this->getSetupVersion());
        $setupVersion->setData($this->getData());
        $setupVersion->run();

        return $this;
    }
    protected function _afterRun() {
        $installer = $this->getInstaller();

        if (method_exists($installer->getConnection(), 'disallowDdlCache')) {
            $installer->getConnection()->disallowDdlCache();
        }
        $installer->endSetup();

        return $this;
    }

    public function scheduleReindexing($code) {
        Mage::helper('mana_core/db')->scheduleReindexing($code);
    }
}