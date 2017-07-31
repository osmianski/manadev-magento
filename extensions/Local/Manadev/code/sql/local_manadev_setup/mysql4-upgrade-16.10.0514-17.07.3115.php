<?php
/* BASED ON SNIPPET: Resources/Install/upgrade script */
/* @var $installer Local_Manadev_Resource_Setup */
$installer = $this;

$installer->startSetup();
$table = "m_composer_repo";
$installer->run("
    CREATE TABLE `{$this->getTable($table)}` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `key` varchar(40) NOT NULL,
      `title` varchar(40) NOT NULL DEFAULT '',
      `customer_id` int(10) unsigned NOT NULL,
      `require_user_credentials` tinyint NOT NULL DEFAULT 1,
      PRIMARY KEY (`id`),
      KEY `customer_id` (`customer_id`),
      KEY `key` (`key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';

    ALTER TABLE `{$this->getTable($table)}`
        ADD CONSTRAINT `FK_{$this->getTable($table)}_customer` FOREIGN KEY (`customer_id`)
            REFERENCES `{$this->getTable('customer_entity')}` (`entity_id`)
            ON DELETE CASCADE ON UPDATE CASCADE;

    ALTER TABLE `{$this->getTable('downloadable_link_purchased_item')}`
      ADD COLUMN `composer_repo_id` bigint(20) NULL,
      ADD KEY `composer_repo_id` (`composer_repo_id`),
      ADD CONSTRAINT `FK_{$this->getTable('downloadable_link_purchased_item')}_$table` FOREIGN KEY (`composer_repo_id`)
        REFERENCES `{$this->getTable($table)}` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE;
");
$installer->endSetup();