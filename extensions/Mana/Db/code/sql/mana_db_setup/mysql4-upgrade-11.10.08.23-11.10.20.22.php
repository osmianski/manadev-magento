<?php 

/* BASED ON SNIPPET: Resources/Install/upgrade script */
if (defined('COMPILER_INCLUDE_PATH')) {
	throw new Exception(Mage::helper('mana_core')->__('This Magento installation contains pending database installation/upgrade scripts. Please turn off Magento compilation feature while installing/upgrading new modules in Admin Panel menu System->Tools->Compilation.'));
}

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/* BASED ON SNIPPET: Resources/Table creation/alteration script */
$table = 'mana_db/edit_session';
if (!$installer->tableExists($installer->getTable($table))) {
    $installer->run("
        CREATE TABLE `{$installer->getTable($table)}` (
            `id` BIGINT NOT NULL auto_increment,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        INSERT INTO `{$installer->getTable($table)}` (`id`) VALUES (0);
    ");
}
$installer->endSetup();

