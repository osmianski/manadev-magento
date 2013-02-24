<?php
/**
 * @category    Mana
 * @package     ManaPro_Video
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

$table = 'manapro_video/video';
$installer->run("
  ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (
      `label` varchar(128) NOT NULL default ''
  );
  ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (
      `thumbnail` varchar(128) NOT NULL default ''
  );
");

$table = 'manapro_video/video_store';
$installer->run("
  ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (
      `label` varchar(128) NOT NULL default ''
  );
  ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (
      `thumbnail` varchar(128) NOT NULL default ''
  );
");


if (method_exists($this->getConnection(), 'disallowDdlCache')) {
    $this->getConnection()->disallowDdlCache();
}
$installer->endSetup();

