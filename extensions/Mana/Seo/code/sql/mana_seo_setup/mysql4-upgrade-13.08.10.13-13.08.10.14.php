<?php
/**
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/* @var $setup Mana_Db_Model_Setup */
$setup = Mage::getModel('mana_db/setup');
$setup->run($this, 'mana_seo', '13.08.10.14');

/* @var $db Mana_Db_Helper_Data */
$db = Mage::helper('mana_db');

/* @var $collection Mana_Db_Resource_Entity_Collection */
$collection = $db->getResourceModel('mana_seo/schema/global_collection');
foreach ($collection as $schema) {
    /* @var $schema Mana_Seo_Model_Schema */
    $schema->overrideCategorySeparator('/')->save();
}

$setup->scheduleReindexing('mana_db');
