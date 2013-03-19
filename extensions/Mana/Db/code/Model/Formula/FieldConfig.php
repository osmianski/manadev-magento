<?php
/** 
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Db_Model_Formula_FieldConfig extends Varien_Object {
    /**
     * @var Mana_Db_Model_Formula_Field[]
     */
    protected $_fields = array();

    /**
     * @return Mana_Db_Model_Formula_Field[]
     */
    public function getFields() {
        return $this->_fields;
    }
}