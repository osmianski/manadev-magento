<?php
/** 
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method string getAlias()
 * @method Mana_Db_Model_Formula_Context_Table setAlias(string $value)
 * @method string getName()
 * @method Mana_Db_Model_Formula_Context_Table setName(string $value)
 */
class Mana_Db_Model_Formula_Context_Table extends Varien_Object {
    /**
     * @var Mana_Db_Model_Formula_Context_Join[]
     */
    protected $_joins = array();

    /**
     * @return Mana_Db_Model_Formula_Context_Join[]
     */
    public function getJoins() {
        return $this->_joins;
    }

    /**
     * @param string $name
     * @return Mana_Db_Model_Formula_Context_Join|null
     */
    public function getJoin($name) {
        return isset($this->_joins[$name]) ? $this->_joins[$name] : null;
    }

    /**
     * @param string $name
     * @param Mana_Db_Model_Formula_Context_Join $value
     * @return Mana_Db_Model_Formula_Context_Table
     */
    public function addJoin($name, $value) {
        $value->setAlias($name);
        $this->_joins[$name] = $value;

        return $this;
    }
}