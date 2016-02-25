<?php
/**
 * @category    Mana
 * @package     Mana_Sorting
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Sorting_Model_Method_StoreCustomSettings extends Mana_Sorting_Model_Method_Abstract{
    const ENTITY = 'mana_sorting/method_storeCustomSettings';

    protected function _construct() {
        $this->_init(self::ENTITY);
    }
}