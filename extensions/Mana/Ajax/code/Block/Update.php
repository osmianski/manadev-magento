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

		$js
		    ->setConfig('debug', Mage::app()->getStore()->isAdmin() || Mage::getStoreConfigFlag('mana/ajax/debug'))
		    ->setConfig('showOverlay', Mage::getStoreConfigFlag('mana/ajax/overlay'))
		    ->setConfig('showWait', Mage::getStoreConfigFlag('mana/ajax/progress'))
		    ->setConfig('ajax.enabled', $ajax->isEnabled());
		Mage::dispatchEvent('m_ajax_options');
        return '';
    }

    public function addUpdatedBlocksIfPageChanged($blocks) {
        $this->setUpdatedBlocksIfPageChanged($this->getUpdatedBlocksIfPageChanged()
            ? $this->getUpdatedBlocksIfPageChanged() .',' . $blocks
            : $blocks
        );

        return $this;
    }

    public function addUpdatedBlocksIfParameterChanged($blocks) {
        $this->setUpdatedBlocksIfParameterChanged($this->getUpdatedBlocksIfParameterChanged()
            ? $this->getUpdatedBlocksIfParameterChanged() .',' . $blocks
            : $blocks
        );

        return $this;
    }
}