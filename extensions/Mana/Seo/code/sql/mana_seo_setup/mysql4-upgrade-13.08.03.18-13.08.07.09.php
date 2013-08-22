<?php
/**
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

Mage::register('m_prevent_indexing_on_save', true, true);

/* @var $setup Mana_Db_Model_Setup */
$setup = Mage::getModel('mana_db/setup');
$setup->run($this, 'mana_seo', '13.08.07.09');

Mage::unregister('m_prevent_indexing_on_save');
$setup->scheduleReindexing('mana_db');
$setup->scheduleReindexing('mana_seo_url');