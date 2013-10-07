<?php
/**
 * @category    Mana
 * @package     Mana_Lt
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Lt_Resource_Rate_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    protected function _construct() {
        $this->_init('mana_lt/rate');
    }
}