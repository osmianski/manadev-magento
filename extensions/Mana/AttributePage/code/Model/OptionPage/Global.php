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
class Mana_AttributePage_Model_OptionPage_Global extends Mana_AttributePage_Model_OptionPage_Abstract {
    const ENTITY = 'mana_attributepage/optionPage_global';

    protected function _construct() {
        $this->_init(self::ENTITY);
    }
}