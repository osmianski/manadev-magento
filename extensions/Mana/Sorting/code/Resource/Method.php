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
class Mana_Sorting_Resource_Method extends Mana_Sorting_Resource_Method_Abstract {
    protected function _construct() {
        $this->_init(Mana_Sorting_Model_Method::ENTITY, 'id');
    }

}