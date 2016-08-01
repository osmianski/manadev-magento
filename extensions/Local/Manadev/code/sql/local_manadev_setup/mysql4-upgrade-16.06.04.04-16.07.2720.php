<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;

$installer->startSetup();
$table = "downloadable_link_purchased_item";
$customerTable = "customer_entity";
$installer->run("
    ALTER TABLE `{$this->getTable($table)}` ADD COLUMN (`m_free_customer_id` INT(10) UNSIGNED NULL);
    ALTER TABLE `{$this->getTable($table)}`
        ADD CONSTRAINT `FK_{$this->getTable($table)}_customer` FOREIGN KEY (`m_free_customer_id`)
            REFERENCES `{$this->getTable($customerTable)}` (`entity_id`)
            ON DELETE CASCADE ON UPDATE CASCADE;
");
$installer->endSetup();