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
class ManaPro_FilterContent_Model_Rule_StoreCustomSettings extends ManaPro_FilterContent_Model_Rule_Abstract {
    const ENTITY = 'manapro_filtercontent/rule_storeCustomSettings';

    protected function _construct() {
        $this->_init(self::ENTITY);
    }
}