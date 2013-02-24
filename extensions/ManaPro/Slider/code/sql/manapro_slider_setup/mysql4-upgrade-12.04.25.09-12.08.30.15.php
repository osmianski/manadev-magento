<?php
/**
 * @category    Mana
 * @package     ManaPro_Slider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

if (defined('COMPILER_INCLUDE_PATH')) {
    throw new Exception(Mage::helper('mana_core')->__('This Magento installation contains pending database installation/upgrade scripts. Please turn off Magento compilation feature while installing/upgrading new modules in Admin Panel menu System->Tools->Compilation.'));
}

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
if (method_exists($this->getConnection(), 'allowDdlCache')) {
    $this->getConnection()->allowDdlCache();
}

$table = 'manapro_slider/item';
$installer->run("
  DROP TABLE IF EXISTS `{$this->getTable($table)}`;
");

$defaultPosition = ManaPro_Slider_Block_Slider::DEFAULT_POSITION;
foreach (array('manapro_slider/product' => 'product_id', 'manapro_slider/cmsblock' => 'block_id') as $table => $foreignKey) {
    $installer->run("
      DROP TABLE IF EXISTS `{$this->getTable($table)}`;
      CREATE TABLE `{$this->getTable($table)}` (
        `id` bigint NOT NULL AUTO_INCREMENT,
        `{$foreignKey}` int(10) unsigned NULL,
        `position` int NOT NULL default '{$defaultPosition}',

        `edit_session_id` bigint NOT NULL default '0',
        `edit_status` bigint NOT NULL default '0',
        `edit_massaction` tinyint NOT NULL default '0',

        PRIMARY KEY  (`id`),
        KEY `{$foreignKey}` (`{$foreignKey}`),
        KEY `position` (`position`),
        KEY `edit_session_id` (`edit_session_id`),
        KEY `edit_status` (`edit_status`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='';

      ALTER TABLE `{$this->getTable($table)}`
          ADD CONSTRAINT `FK_{$this->getTable($table)}_mana_db/edit_session` FOREIGN KEY (`edit_session_id`)
          REFERENCES `{$installer->getTable('mana_db/edit_session')}` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
    ");
}


if (method_exists($this->getConnection(), 'disallowDdlCache')) {
    $this->getConnection()->disallowDdlCache();
}
$installer->endSetup();
