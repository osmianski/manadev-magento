<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterContent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterContent_Resource_Rule_StoreCustomSettings  extends ManaPro_FilterContent_Resource_Rule_Abstract {
    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_init(ManaPro_FilterContent_Model_Rule_StoreCustomSettings::ENTITY, 'id');
    }
}