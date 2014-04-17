<?php
/** 
 * @category    Mana
 * @package     Mana_Sorting
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

$contentRuleTables = array(
    $this->getTable('manapro_filtercontent/rule_globalCustomSettings'),
    $this->getTable('manapro_filtercontent/rule_global'),
    $this->getTable('manapro_filtercontent/rule_storeCustomSettings'),
    $this->getTable('manapro_filtercontent/rule_store'),
);

foreach ($contentRuleTables as $table) {
    $installer->run("
        ALTER TABLE `$table`
            ADD COLUMN (`background_image` varchar(255) NOT NULL DEFAULT '');
    ");
}

$filterValueTables = array(
    $this->getTable('mana_filters/filter2_value'),
    $this->getTable('mana_filters/filter2_value_store'),
);

foreach ($filterValueTables as $table) {
    $installer->run("
        ALTER TABLE `$table`
            ADD COLUMN (`content_background_image` varchar(255) NOT NULL DEFAULT '');
    ");
}

if (method_exists($this->getConnection(), 'disallowDdlCache')) {
    $this->getConnection()->disallowDdlCache();
}
$installer->endSetup();

