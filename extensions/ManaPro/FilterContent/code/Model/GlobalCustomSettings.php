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
class ManaPro_FilterContent_Model_GlobalCustomSettings extends ManaPro_FilterContent_Model_Abstract {
    const ENTITY = 'manapro_filtercontent/globalCustomSettings';

    protected function _construct() {
        $this->_init(self::ENTITY);
    }
}