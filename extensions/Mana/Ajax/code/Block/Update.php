<?php
/**
 * @category    Mana
 * @package     Mana_Ajax
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

class Mana_Ajax_Block_Update extends Mage_Core_Block_Abstract {
    protected function _toHtml()
    {
		/* @var $ajax Mana_Ajax_Helper_Data */
		$ajax = Mage::helper('mana_ajax');

		/* @var $js Mana_Core_Helper_Js */
		$js = Mage::helper('mana_core/js');

        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

		$js
		    ->setConfig('debug', Mage::getStoreConfigFlag('mana/ajax/debug'))
		    ->setConfig('showOverlay', Mage::getStoreConfigFlag('mana/ajax/overlay'))
		    ->setConfig('showWait', Mage::getStoreConfigFlag('mana/ajax/progress'))
		    ->setConfig('ajax.enabled', $ajax->isEnabled())
		    ->setConfig('ajax.currentRoute', $core->getRoutePath() . $core->getRouteParams());
		Mage::dispatchEvent('m_ajax_options');
        return '';
    }
}