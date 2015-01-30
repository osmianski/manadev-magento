<?php
/** 
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Content_Resource_Page_GlobalCustomSettings_Collection extends Mana_Content_Resource_Page_Abstract_Collection {
    protected function _construct() {
        $this->_init(Mana_Content_Model_Page_GlobalCustomSettings::ENTITY);
    }
}