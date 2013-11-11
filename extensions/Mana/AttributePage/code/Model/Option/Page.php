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
class Mana_AttributePage_Model_Option_Page extends Mana_Db_Model_Entity {
    public function canShow() {
        if (!$this->getId()) {
            return false;
        }

        if (!$this->getData('is_active')) {
            return false;
        }
        return true;
    }
}