<?php
/**
 * @category    Mana
 * @package     Mana_GeoLocation
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

if (defined('COMPILER_INCLUDE_PATH')) {
    throw new Exception(Mage::helper('mana_core')->__('This Magento installation contains pending database installation/upgrade scripts. Please turn off Magento compilation feature while installing/upgrading new modules in Admin Panel menu System->Tools->Compilation.'));
}

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
if (method_exists($this->getConnection(), 'allowDdlCache')) {
    $this->getConnection()->allowDdlCache();
}

$table = 'mana_geolocation/domain';
$installer->run("
  DROP TABLE IF EXISTS `{$this->getTable($table)}`;
  CREATE TABLE `{$this->getTable($table)}` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `domain` varchar(5) NOT NULL default '',
    `country_id` varchar(2) NOT NULL default '',

    PRIMARY KEY  (`id`),
    KEY `domain` (`domain`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';

");

foreach (array('mana_geolocation/ip4', 'mana_geolocation/ip6') as $table) {
    $installer->run("
      ALTER TABLE `{$this->getTable($table)}` ADD KEY `ip_from` (`ip_from`);
      ALTER TABLE `{$this->getTable($table)}` ADD KEY `ip_to` (`ip_to`);
    ");
}

if (method_exists($this->getConnection(), 'disallowDdlCache')) {
    $this->getConnection()->disallowDdlCache();
}
$installer->endSetup();

if (!Mage::registry('m_run_db_replication')) {
    Mage::register('m_run_db_replication', true);
}
