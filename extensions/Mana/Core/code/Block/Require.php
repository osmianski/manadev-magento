<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Core_Block_Require extends Mage_Core_Block_Template {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('mana/core/require.phtml');
    }

    public function getBaseRequireUrl() {
        return Mage::getBaseUrl('js').'m-classes';
    }
}