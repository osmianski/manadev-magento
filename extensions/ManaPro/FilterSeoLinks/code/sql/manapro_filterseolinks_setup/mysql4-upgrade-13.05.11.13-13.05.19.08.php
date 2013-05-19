<?php
/**
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/* @var $setup Mana_Db_Model_Setup */
$setup = Mage::getModel('mana_db/setup');
$setup->run($this, 'manapro_filterseolinks', '13.05.19.08');

/* @var $db Mana_Db_Helper_Data */
$db = Mage::helper('mana_db');

/* @var $schema Mana_Seo_Model_Schema */
$schema = $db->getModel('mana_seo/schema')->load('manadev-2013', 'internal_name');
$schema
    ->setIncludeFilterName(Mana_Seo_Model_Schema::INCLUDE_IF_NECESSARY)
    ->setRedirectToSubcategory(1)
    ->save();

$schema = $db->getModel('mana_seo/schema')->load('manadev-2011', 'internal_name');
$schema
    ->setIncludeFilterName(Mana_Seo_Model_Schema::INCLUDE_ALWAYS)
    ->setRedirectToSubcategory(0)
    ->save();

$setup->scheduleReindexing('mana_seo');