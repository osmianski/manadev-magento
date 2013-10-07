<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/* BASED ON SNIPPET: Resources/Table creation/alteration script */
$table = 'm_request';
$installer->run("
DROP TABLE IF EXISTS `{$this->getTable($table)}`;
	CREATE TABLE `{$this->getTable($table)}` (
	  `id` int(10) NOT NULL AUTO_INCREMENT,
	  `description` text NOT NULL,
	  `customer_id` int(10) unsigned NULL,
	  `files` text NOT NULL,
	  PRIMARY KEY  (`id`),
	  KEY `customer_id` (`customer_id`)
	  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';
	");

// INSERT HERE: actual installation steps

$installer->endSetup();