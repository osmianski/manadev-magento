<?php
/**
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/* @var $setup Mana_Db_Model_Setup */
$setup = Mage::getModel('mana_db/setup');

/* @var $db Mana_Db_Helper_Data */
$db = Mage::helper('mana_db');

/* @var $utils Mana_Core_Helper_Utils */
$utils = Mage::helper('mana_core/utils');

/* @var $schema Mana_Seo_Model_Schema */
$schema = $db->getModel('mana_seo/schema')->load('manadev-2013', 'internal_name');
$schema
    ->setQuerySeparator('/')
    ->setParamSeparator('/')
    ->setFirstValueSeparator('/')
    ->setMultipleValueSeparator('-')
    ->setUseFilterLabels(1)
    ->setToolbarUrlKeys(json_encode(array(
        array('internal_name' => 'p', 'name' => 'page', 'position' => 9900),
        array('internal_name' => 'order', 'name' => 'sort-by', 'position' => 9910),
        array('internal_name' => 'dir', 'name' => 'sort-direction', 'position' => 9920),
        array('internal_name' => 'mode', 'name' => 'mode', 'position' => 9930),
        array('internal_name' => 'limit', 'name' => 'show', 'position' => 9940),
    )))
    ->save();

$schema = $db->getModel('mana_seo/schema')->load('manadev-2011', 'internal_name');
$schema
    ->setQuerySeparator('/' . $utils->getStoreConfig('mana_filters/seo/conditional_word') . '/')
    ->setParamSeparator('/')
    ->setFirstValueSeparator('/')
    ->setMultipleValueSeparator('_')
    ->setUseFilterLabels($utils->getStoreConfig('mana_filters/seo/use_label'))
    ->setToolbarUrlKeys(json_encode(array(
        array('internal_name' => 'p', 'name' => 'p', 'position' => 9900),
        array('internal_name' => 'order', 'name' => 'order', 'position' => 9910),
        array('internal_name' => 'dir', 'name' => 'dir', 'position' => 9920),
        array('internal_name' => 'mode', 'name' => 'mode', 'position' => 9930),
        array('internal_name' => 'limit', 'name' => 'limit', 'position' => 9940),
    )))
    ->save();

$setup->scheduleReindexing('mana_seo');