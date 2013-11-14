<?php
/** 
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_AttributePage_Resource_AttributePage_Store extends Mana_AttributePage_Resource_AttributePage_Abstract  {
    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_init(Mana_AttributePage_Model_AttributePage_Store::ENTITY, 'id');
    }
}