<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
    ALTER TABLE `m_license_instance`
      ADD `remote_ip` VARCHAR(50) NOT NULL AFTER `frontend_urls`,
      ADD `base_dir` VARCHAR(255) NOT NULL AFTER `remote_ip`,
      ADD `magento_version` VARCHAR(20) NOT NULL AFTER `base_dir`;
");
$installer->endSetup();