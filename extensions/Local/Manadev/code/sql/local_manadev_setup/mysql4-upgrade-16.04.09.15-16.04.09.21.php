<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
    CREATE TABLE `m_license_store` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `instance_id` bigint(20) NOT NULL,
      `frontend_url` TEXT NOT NULL,
      `theme` varchar(255) NOT NULL,
      `store_id` bigint(20) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_key` (`instance_id`, `store_id`)
    )  ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';
");
$installer->endSetup();