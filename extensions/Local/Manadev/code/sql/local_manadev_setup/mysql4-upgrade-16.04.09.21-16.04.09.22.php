<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
    ALTER TABLE `m_license_instance`
      DROP `frontend_urls`,
      DROP `updated_at`;
");

$installer->run("
    RENAME TABLE `m_license_instance` TO `m_license_request`;
");

$installer->run("
    ALTER TABLE `m_license_module` CHANGE COLUMN `instance_id` `request_id`  bigint(20) NOT NULL;
");

$installer->run("
    ALTER TABLE `m_license_extension` CHANGE COLUMN `instance_id` `request_id`  bigint(20) NOT NULL;
");

$installer->run("
    ALTER TABLE `m_license_store` CHANGE COLUMN `instance_id` `request_id`  bigint(20) NOT NULL;
");

$installer->run("
    ALTER TABLE `m_license_request` DROP INDEX `unique_key`;
");

$installer->endSetup();