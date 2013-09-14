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
class Mana_Core_Test_Case extends PHPUnit_Framework_TestCase {
    protected static $_mergedTestXml;

    public static function installTestData() {
        if (!($testVariation = Mage::getStoreConfig('mana/developer/test_db'))) {
            throw new Exception('Tests can only be run on test database. Please either switch to test database ' .
                'by using team-test:switch command');
        }
        if ($testVariation != (string) Mage::getConfig()->getNode('mana_developertools/test/variation')) {
            throw new Exception('Test variation name in System->Configuration is out of sync with app/etc/local.xml file');
        }

        $installedVersions = self::_beginInstallTestData($testVariation);
        try {
            foreach (Mage::getConfig()->getNode('modules')->children() as $moduleName => $moduleXml) {
                if ($definedVersion = (string)$moduleXml->version) {
                    $installedVersion = isset($installedVersions[$moduleName]) ? $installedVersions[$moduleName] : '';
                    if (!$installedVersion || version_compare($definedVersion, $installedVersion) > 0) {
                        $setup = new Mana_Core_Test_Setup($testVariation, $moduleName, $definedVersion, $installedVersion);
                        $setup->install();
                    }
                    self::_finishModuleInstall($moduleName, $definedVersion);
                }
            }
            self::_commitTestData();
        }
        catch(Exception $e) {
            self::_rollbackTestData();
            throw $e;
        }
    }

    protected static function _beginInstallTestData() {
        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');

        /* @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = $res->getConnection('setup');

        $q = "
          CREATE TABLE  IF NOT EXISTS `{$res->getTableName('m_test_version')}` (
            `module` varchar(80) NOT NULL,
            `version` varchar(20) NOT NULL,

            PRIMARY KEY  (`module`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';
        ";

        $db->query($q);

        // we can't use translations here as some Magento code is not executed under nested transactions
        //$db->beginTransaction();

        return $db->fetchPairs("SELECT `module`, `version` FROM `{$res->getTableName('m_test_version')}`");
    }

    protected static function _finishModuleInstall($moduleName, $version) {
        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');

        /* @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = $res->getConnection('setup');

        $q = "INSERT INTO `{$res->getTableName('m_test_version')}` ".
            "(`module`, `version`) ".
            "VALUES('$moduleName', '$version') ".
            "ON DUPLICATE KEY UPDATE `version` = '$version'";

        $db->query($q);
    }

    protected static function _commitTestData() {
        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');

        /* @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = $res->getConnection('setup');

        // we can't use translations here as some Magento code is not executed under nested transactions
        //$db->commit();
    }

    protected static function _rollbackTestData() {
        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');

        /* @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = $res->getConnection('setup');

        // we can't use translations here as some Magento code is not executed under nested transactions
        //$db->rollback();
    }

    public static function getMergedTestXml() {

    }
}