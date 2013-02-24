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
class ManaPro_Slider_Resource_Product extends Mana_Db_Resource_Object {
    protected function _construct() {
        $this->_init('manapro_slider/product', 'id');
        $this->_isPkAutoIncrement = false;
    }
    protected function _addEditedData($object, $fields, $useDefault) {
        Mage::helper('mana_db')->updateDefaultableField($object, 'edit_massaction', 0, $fields, $useDefault);
        Mage::helper('mana_db')->updateDefaultableField($object, 'product_id', 1, $fields, $useDefault);
        Mage::helper('mana_db')->updateDefaultableField($object, 'position', 2, $fields, $useDefault);
        Mage::helper('mana_db')->updateDefaultableField($object, 'image_index', 3, $fields, $useDefault);
    }
}