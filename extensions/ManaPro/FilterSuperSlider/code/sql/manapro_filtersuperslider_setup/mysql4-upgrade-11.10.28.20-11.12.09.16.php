<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterSuperSlider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
if (defined('COMPILER_INCLUDE_PATH')) {
	throw new Exception(Mage::helper('mana_core')->__('This Magento installation contains pending database installation/upgrade scripts. Please turn off Magento compilation feature while installing/upgrading new modules in Admin Panel menu System->Tools->Compilation.'));
}
/* @var $installer Mage_Core_Model_Resource_Setup */$installer = $this;
$installer->startSetup();

$table = 'mana_filters/filter2';
$installer->run("
    ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (
        `slider_decimal_digits` tinyint NOT NULL default '0'
    );
    ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (
        `slider_decimal_digits2` tinyint NOT NULL default '0'
    );
");

$table = 'mana_filters/filter2_store';
$installer->run("
    ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (
        `slider_decimal_digits` tinyint NOT NULL default '0'
    );
    ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (
        `slider_decimal_digits2` tinyint NOT NULL default '0'
    );
");

$installer->endSetup();

if (!Mage::registry('m_run_db_replication')) {
    Mage::register('m_run_db_replication', true);
}
