<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/* BASED ON SNIPPET: Resources/Table creation/alteration script */
$table = 'm_download';
$installer->run("
	DROP TABLE IF EXISTS `{$this->getTable($table)}`;
	CREATE TABLE `{$this->getTable($table)}` (
	  `id` int(10) NOT NULL AUTO_INCREMENT,
	  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
	  `product_id` int(10) unsigned NULL,
	  `ip_address` varchar(30) NOT NULL default '',
	  `is_guest` tinyint(1) NOT NULL default '0',
	  `customer_id` int(10) unsigned NULL,
	  PRIMARY KEY  (`id`),
	  KEY `product_id` (`product_id`),
	  KEY `customer_id` (`customer_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';
	
");

/* BASED ON SNIPPET: Resources/Table creation/alteration script */
$table = 'm_download_failure';
$installer->run("
	DROP TABLE IF EXISTS `{$this->getTable($table)}`;
	CREATE TABLE `{$this->getTable($table)}` (
	  `id` int(10) NOT NULL AUTO_INCREMENT,
	  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
	  `message` varchar(255) NOT NULL default '',
	  `product_id` int(10) unsigned NULL,
	  `ip_address` varchar(30) NOT NULL default '',
	  `is_guest` tinyint(1) NOT NULL default '0',
	  `customer_id` int(10) unsigned NULL,
	  PRIMARY KEY  (`id`),
	  KEY `product_id` (`product_id`),
	  KEY `customer_id` (`customer_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';
	
");

// INSERT HERE: actual installation steps

$installer->endSetup();