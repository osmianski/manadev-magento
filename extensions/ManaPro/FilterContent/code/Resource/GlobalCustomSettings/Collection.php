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
class ManaPro_FilterContent_Resource_GlobalCustomSettings_Collection extends ManaPro_FilterContent_Resource_Abstract_Collection {
    protected function _construct() {
        $this->_init(ManaPro_FilterContent_Model_GlobalCustomSettings::ENTITY);
    }
}