<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("
    CREATE TABLE `m_license_instance` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `magento_id` varchar(50) NOT NULL,
      `admin_url` varchar(255) NOT NULL,
      `frontend_urls` TEXT NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_key` (`magento_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';
");

$installer->run("
    CREATE TABLE `m_license_module` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `instance_id` bigint(20) NOT NULL,
      `module` varchar(50) NOT NULL,
      `version` varchar(50) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_key` (`instance_id`, `module`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';
");

$installer->run("
    CREATE TABLE `m_license_extension` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `instance_id` bigint(20) NOT NULL,
      `license_verification_no` varchar(50) NULL,
      `code` varchar(255) NOT NULL,
      `version` varchar(50) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_key` (`instance_id`, `code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';
");

$installer->endSetup();