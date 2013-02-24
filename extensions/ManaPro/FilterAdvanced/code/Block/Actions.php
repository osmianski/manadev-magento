<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterAdvanced
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterAdvanced_Block_Actions extends Mage_Core_Block_Template {
    public function sortActions($actions) {
        usort($actions, array($this, '_compareActions'));
        return $actions;
    }
    public function _compareActions($a, $b) {
        if ($a['position'] < $b['position']) return -1;
        if ($a['position'] > $b['position']) return 1;
        return 0;
    }
}