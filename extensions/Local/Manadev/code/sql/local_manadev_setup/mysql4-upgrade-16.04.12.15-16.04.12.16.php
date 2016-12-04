<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;

$installer->startSetup();


$installer->run("
    ALTER TABLE `m_license_extension`
        ADD CONSTRAINT `FK_m_license_extension_request` FOREIGN KEY (`request_id`)
            REFERENCES `m_license_request` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE;

    ALTER TABLE `m_license_module`
        ADD CONSTRAINT `FK_m_license_module_request` FOREIGN KEY (`request_id`)
            REFERENCES `m_license_request` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE;

    ALTER TABLE `m_license_store`
        ADD CONSTRAINT `FK_m_license_store_request` FOREIGN KEY (`request_id`)
            REFERENCES `m_license_request` (`id`)
            ON DELETE CASCADE ON UPDATE CASCADE;
");

$installer->endSetup();