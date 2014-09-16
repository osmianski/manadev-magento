<?php
/**
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

/* @var $setup Mana_Db_Model_Setup */
$setup = Mage::getModel('mana_db/setup');

if (method_exists($this->getConnection(), 'allowDdlCache')) {
    $this->getConnection()->allowDdlCache();
}

// seo url: additional field for deleting URL keys
$table = $this->getTable('mana_seo/url');
$installer->run("
    ALTER TABLE `$table`
        ADD COLUMN `special_filter_id` bigint(20) DEFAULT NULL,
        ADD KEY `special_filter_id` (`special_filter_id`);
");

if (method_exists($this->getConnection(), 'disallowDdlCache')) {
    $this->getConnection()->disallowDdlCache();
}

$setup->scheduleReindexing('mana_seo_url');
