<?php
/** 
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 */
class Mana_Db_Model_Formula_Node {
    public function __construct($data = array()) {
        foreach ($data as $property => $value) {
            $this->$property = $value;
        }
    }
}