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
class Mana_Content_Resource_Page_StoreCustomSettings extends Mana_Content_Resource_Page_Abstract {
    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_init(Mana_Content_Model_Page_StoreCustomSettings::ENTITY, 'id');
    }
}