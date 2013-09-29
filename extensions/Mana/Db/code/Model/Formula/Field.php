<?php
/** 
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method string getName()
 * @method Mana_Db_Model_Formula_Field setName(string $value)
 * @method string getType()
 * @method Mana_Db_Model_Formula_Field setType(string $value)
 * @method string getRole()
 * @method Mana_Db_Model_Formula_Field setRole(string $value)
 * @method int getNo()
 * @method Mana_Db_Model_Formula_Field setNo(int $value)
 * @method bool hasFormula()
 * @method Mana_Db_Model_Formula_Node getFormula()
 * @method Mana_Db_Model_Formula_Field setFormula(Mana_Db_Model_Formula_Node $value)
 * @method bool hasValue()
 * @method string getValue()
 * @method Mana_Db_Model_Formula_Field setValue(string $value)
 * @method bool hasDependencies()
 * @method string[] getDependencies()
 * @method Mana_Db_Model_Formula_Field setDependencies(array $value)
 * @method string getFormulaString()
 * @method Mana_Db_Model_Formula_Field setFormulaString(string $value)
 */
class Mana_Db_Model_Formula_Field extends Varien_Object {
    /**
     * @param Mana_Db_Model_Entity $model
     * @return bool
     */
    public function useDefault($model) {
    }
}