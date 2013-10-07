<?php
/**
 * @category    Mana
 * @package     Mana_Lt
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

$table = 'mana_lt/rate';
$installer->run("
  DROP TABLE IF EXISTS `{$this->getTable($table)}`;
  CREATE TABLE `{$this->getTable($table)}` (
    `id` bigint NOT NULL AUTO_INCREMENT,
    `currency_code` varchar(5) NOT NULL default '',
    `date` varchar(20) NOT NULL default '',
    `rate` decimal(12,4) NOT NULL default '1',

    PRIMARY KEY  (`id`),
    KEY `currency_code` (`currency_code`),
    KEY `date` (`date`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';

");

if (method_exists($this->getConnection(), 'disallowDdlCache')) {
    $this->getConnection()->disallowDdlCache();
}
$installer->endSetup();

