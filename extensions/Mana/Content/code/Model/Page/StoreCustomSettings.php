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
class Mana_Content_Model_Page_StoreCustomSettings extends Mana_Content_Model_Page_Abstract {
    const ENTITY = 'mana_content/page_storeCustomSettings';

    protected $rules = array(
    );

    protected function _construct() {
        $this->_init(self::ENTITY);
    }
}