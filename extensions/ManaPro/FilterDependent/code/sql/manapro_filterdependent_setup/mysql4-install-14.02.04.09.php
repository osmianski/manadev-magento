<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterAdmin
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

$table = $this->getTable('mana_filters/filter2');
$installer->run("
    ALTER TABLE `$table`
        ADD COLUMN (`depends_on_filter_id` bigint NULL),
        ADD KEY `depends_on_filter_id` (`depends_on_filter_id`),
        ADD CONSTRAINT `FK_{$table}_depends` FOREIGN KEY (`depends_on_filter_id`)
            REFERENCES `$table` (`id`)
            ON DELETE SET NULL ON UPDATE SET NULL;
");

if (method_exists($this->getConnection(), 'disallowDdlCache')) {
    $this->getConnection()->disallowDdlCache();
}
$installer->endSetup();
