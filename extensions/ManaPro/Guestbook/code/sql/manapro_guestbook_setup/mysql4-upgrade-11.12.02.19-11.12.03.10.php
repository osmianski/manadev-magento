<?php
/** 
 * @category    Mana
 * @package     ManaPro_Guestbook
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
if (defined('COMPILER_INCLUDE_PATH')) {
	throw new Exception(Mage::helper('mana_core')->__('This Magento installation contains pending database installation/upgrade scripts. Please turn off Magento compilation feature while installing/upgrading new modules in Admin Panel menu System->Tools->Compilation.'));
}
/* @var $installer Mage_Core_Model_Resource_Setup */$installer = $this;
$installer->startSetup();

$table = 'manapro_guestbook/post';
$installer->run("
  ALTER TABLE `{$this->getTable($table)}` ADD COLUMN ( 
      `url` varchar(255) NOT NULL default ''
  );
  ALTER TABLE `{$this->getTable($table)}` ADD COLUMN ( 
      `created_at` datetime NOT NULL default '0000-00-00 00:00:00'
  );
");

$installer->endSetup();