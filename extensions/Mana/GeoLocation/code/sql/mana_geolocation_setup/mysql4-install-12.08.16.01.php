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

$table = 'mana_geolocation/ip4';
$installer->run("
  DROP TABLE IF EXISTS `{$this->getTable($table)}`;
  CREATE TABLE `{$this->getTable($table)}` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `ip_from` bigint NOT NULL default '0',
    `ip_to` bigint NOT NULL default '0',
    `registry` varchar(40) NOT NULL default '',
    `date_assigned` bigint NOT NULL default '0',
    `country_id` varchar(2) NOT NULL default '',

    PRIMARY KEY  (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';

");

$table = 'mana_geolocation/ip6';
$installer->run("
  DROP TABLE IF EXISTS `{$this->getTable($table)}`;
  CREATE TABLE `{$this->getTable($table)}` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `ip_from` varchar(32) NOT NULL default '0',
    `ip_to` varchar(32) NOT NULL default '0',
    `registry` varchar(40) NOT NULL default '',
    `date_assigned` bigint NOT NULL default '0',
    `country_id` varchar(2) NOT NULL default '',

    PRIMARY KEY  (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';

");

if (method_exists($this->getConnection(), 'disallowDdlCache')) {
    $this->getConnection()->disallowDdlCache();
}
$installer->endSetup();

if (!Mage::registry('m_run_db_replication')) {
    Mage::register('m_run_db_replication', true);
}
