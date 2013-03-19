<?php
/** 
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method string getEntity()
 * @method Mana_Db_Model_Formula_Context setEntity(string $value)
 * @method Mage_Core_Model_Abstract getModel()
 * @method Mana_Db_Model_Formula_Context setModel(Mage_Core_Model_Abstract $value)
 * @method Mana_Db_Helper_Formula_Processor getProcessor()
 * @method mixed getResult()
 * @method Mana_Db_Model_Formula_Context setResult(mixed $value)
 * @method Mana_Db_Model_Formula_Field getField()
 * @method Mana_Db_Model_Formula_Context setField(Mana_Db_Model_Formula_Field $value)
 * @method Mana_Db_Helper_Formula_Abstract getHelper()
 * @method Mana_Db_Model_Formula_Context setHelper(Mana_Db_Helper_Formula_Abstract $value)
 * @method bool getIsAggregate()
 * @method Mana_Db_Model_Formula_Context setIsAggregate(bool $value)
 * @method Mana_Db_Model_Formula_Context getParentContext()
 * @method Mana_Db_Model_Formula_Context setParentContext(Mana_Db_Model_Formula_Context $value)
 */
class Mana_Db_Model_Formula_Context extends Varien_Object {
    /**
     * @var Mana_Db_Model_Formula_Context_Table[]
     */
    protected $_tables = array();

    /**
     * @var string[]
     */
    protected $_columns = array();

    /**
     * @var array
     */
    protected $_parts = array();

    /**
     * @param Mana_Db_Helper_Formula_Processor | string $processor
     * @return Mana_Db_Model_Formula_Context
     */
    public function setProcessor($processor) {
        if (is_string($processor)) {
            $processor = Mage::helper('mana_db/formula_processor_' . $processor);
        }
        $this->setData('processor', $processor);

        return $this;
    }

    /**
     * @return Mana_Db_Model_Formula_Context_Table[]
     */
    public function getTables() {
        return $this->_tables;
    }

    /**
     * @param string $name
     * @return Mana_Db_Model_Formula_Context_Table|null
     */
    public function getTable($name) {
        return isset($this->_tables[$name]) ? $this->_tables[$name] : null;
    }

    /**
     * @param string $name
     * @param Mana_Db_Model_Formula_Context_Table $value
     * @return Mana_Db_Model_Formula_Context
     */
    public function addTable($name, $value) {
        $value->setAlias($name);
        $this->_tables[$name] = $value;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getColumns() {
        return $this->_columns;
    }

    /**
     * @param string $name
     * @return string|Zend_Db_Expr
     */
    public function getColumn($name) {
        return isset($this->_columns[$name]) ? $this->_columns[$name] : null;
    }

    /**
     * @param string $name
     * @param string|Zend_Db_Expr $expression
     * @return Mana_Db_Model_Formula_Context
     */
    public function addColumn($name, $expression) {
        $this->_columns[$name] = $expression;

        return $this;
    }

    /**
     * @return array
     */
    public function getParts() {
        return $this->_parts;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getPart($name) {
        return isset($this->_parts[$name]) ? $this->_parts[$name] : null;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return Mana_Db_Model_Formula_Context
     */
    public function setPart($name, $value) {
        $this->_parts[$name] = $value;

        return $this;
    }

}