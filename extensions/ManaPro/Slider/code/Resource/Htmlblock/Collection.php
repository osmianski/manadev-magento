<?php
/**
 * @category    Mana
 * @package     ManaPro_Slider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Slider_Resource_Htmlblock_Collection extends Mana_Db_Resource_Object_Collection {
    protected function _construct() {
        $this->_init('manapro_slider/htmlblock');
    }
}