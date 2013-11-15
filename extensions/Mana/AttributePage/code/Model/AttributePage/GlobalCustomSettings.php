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
class Mana_AttributePage_Model_AttributePage_GlobalCustomSettings extends Mana_AttributePage_Model_AttributePage_Abstract {
    const ENTITY = 'mana_attributepage/attributePage_globalCustomSettings';

    protected function _construct() {
        $this->_init(self::ENTITY);
    }
}