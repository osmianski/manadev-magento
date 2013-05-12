<?php
/**
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/* @var $setup Mana_Db_Model_Setup */
$setup = Mage::getModel('mana_db/setup');
$setup->run($this, 'mana_seo', '13.05.11.13');

/* @var $db Mana_Db_Helper_Data */
$db = Mage::helper('mana_db');

/* @var $utils Mana_Core_Helper_Utils */
$utils = Mage::helper('mana_core/utils');

/* @var $schema Mana_Seo_Model_Schema */
$schema = $db->getModel('mana_seo/schema');
$schema
    ->setName('MANAdev 2013')
    ->setInternalName('manadev-2013')
    ->setSymbols(json_encode(array(
        array('symbol' => '\\', 'substitute' => ''),
        array('symbol' => '_', 'substitute' => '-'),
        array('symbol' => '\'', 'substitute' => ''),
        array('symbol' => '%', 'substitute' => ''),
        array('symbol' => '#', 'substitute' => ''),
        array('symbol' => '&', 'substitute' => '+'),
        array('symbol' => ' ', 'substitute' => '-'),
    )))
    ->setStatus(Mana_Seo_Model_Schema::STATUS_ACTIVE)
    ->save();

$schema = $db->getModel('mana_seo/schema');
$schema
    ->setName('MANAdev 2011')
    ->setInternalName('manadev-2011')
    ->setSymbols(json_encode(array(
        array('symbol' => '-', 'substitute' => $utils->getStoreConfig('mana_filters/seo/dash')),
        array('symbol' => '/', 'substitute' => $utils->getStoreConfig('mana_filters/seo/slash')),
        array('symbol' => '+', 'substitute' => $utils->getStoreConfig('mana_filters/seo/plus')),
        array('symbol' => '_', 'substitute' => $utils->getStoreConfig('mana_filters/seo/underscore')),
        array('symbol' => '\'', 'substitute' => $utils->getStoreConfig('mana_filters/seo/quote')),
        array('symbol' => '"', 'substitute' => $utils->getStoreConfig('mana_filters/seo/double_quote')),
        array('symbol' => '%', 'substitute' => $utils->getStoreConfig('mana_filters/seo/percent')),
        array('symbol' => '#', 'substitute' => $utils->getStoreConfig('mana_filters/seo/hash')),
        array('symbol' => '&', 'substitute' => $utils->getStoreConfig('mana_filters/seo/ampersand')),
        array('symbol' => ' ', 'substitute' => $utils->getStoreConfig('mana_filters/seo/space')),
    )))
    ->setStatus(Mana_Seo_Model_Schema::STATUS_OBSOLETE)
    ->save();

$setup->scheduleReindexing('mana_seo');