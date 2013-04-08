<?php
/** 
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method string getMode()
 * @method Mana_Db_Model_Formula_Context setMode(string $value)
 * @method string getPrefix()
 * @method Mana_Db_Model_Formula_Context setPrefix(string $value)
 * @method string getEntity()
 * @method Mana_Db_Model_Formula_Context setEntity(string $value)
 * @method string getTargetEntity()
 * @method Mana_Db_Model_Formula_Context setTargetEntity(string $value)
 * @method string getPrimaryEntity()
 * @method Mana_Db_Model_Formula_Context setPrimaryEntity(string $value)
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
 * @method Mana_Db_Model_Formula_Context getAggregateContext()
 * @method Mana_Db_Model_Formula_Context setAggregateContext(Mana_Db_Model_Formula_Context $value)
 * @method string getAlias()
 * @method Mana_Db_Model_Formula_Context setAlias(string $value)
 * @method string __getGlobalEntity()
 * @method Mana_Db_Model_Formula_Context __setGlobalEntity(string $value)
 */
class Mana_Db_Model_Formula_Context extends Varien_Object {
    /**
     * @var Varien_Db_Select()
     */
    protected $_select;
    /**
     * @var string[]
     */
    protected $_fields = array();
    protected $_aliases = array();

    protected $_options = array();

    static protected $_quoteFieldsAndEntities;
    /**
     * @return Mana_Db_Helper_Formula_Entity
     */
    public function getEntityHelper() {
        return Mage::helper('mana_db/formula_entity_' . ($this->getMode() ? $this->getMode() : 'normal'));
    }
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
     * @return Mana_Db_Model_Formula_Context
     */
    public function createChildContext() {
        /* @var $result Mana_Db_Model_Formula_Context */
        $result = Mage::getModel('mana_db/formula_context');
        return $result
            ->setHelper($this->getHelper())
            ->setParentContext($this);
    }

    public function registerAlias($alias) {
        if (!isset($this->_aliases[$alias])) {
            $letter = $this->getPrefix().substr($alias, 0, 1);
            for ($i = 1, $result = $letter; in_array($result, $this->_aliases); $i++, $result = $letter . $i) ;
            $this->_aliases[$alias] = $result;
        }
        return $this->_aliases[$alias];
    }

    public function resolveAliases($expr, $quoteFieldsAndEntities = true) {
        self::$_quoteFieldsAndEntities = $quoteFieldsAndEntities;
        return preg_replace_callback('/{{=([^}]*)}}/', array($this, '_resolveAlias'), $expr);
    }

    protected function _resolveAlias($matches) {
        return $this->resolveAlias($matches[1]);
    }

    public function resolveAlias($fieldExpr) {
        $fieldExpr = explode('.', $fieldExpr);
        foreach ($fieldExpr as $index => $field) {
            $fieldExpr[$index] = trim($field);
        }
        if ($fieldExpr[0] == 'parent') {
            return $this->getParentContext()->resolveAlias(implode('.', array_slice($fieldExpr, 1)));
        }
        elseif (count($fieldExpr) == 2) {
            list($alias, $field) = $fieldExpr;
            if (self::$_quoteFieldsAndEntities) {
                return "`{$this->registerAlias($alias)}`.`$field`";
            }
            else {
                return "{$this->registerAlias($alias)}.$field";
            }
        }
        else {
            throw new Mana_Db_Exception_Formula(Mage::helper('mana_db')->__("Field expression '%s' is expected to be in [alias].[field] format", implode('.', $fieldExpr)));
        }
    }

    /**
     * @return Varien_Db_Select
     */
    public function getSelect() {
        if (!$this->_select) {
            /* @var $resource Mana_Db_Resource_Formula */
            $resource = Mage::getResourceSingleton('mana_db/formula');

            $this->_select = $resource->select();
        }
        return $this->_select;
    }

    public function incrementPrefix() {
        $prefix = $this->getPrefix() . $this->registerAlias($this->getAlias());
        $this->setPrefix($prefix);
        return $prefix;
    }

    public function decrementPrefix() {
        $prefix = substr($this->getPrefix(), 0, strlen($this->getPrefix()) - strlen($this->registerAlias($this->getAlias())));
        $this->setPrefix($prefix);

        return $prefix;
    }

    public function getFields() {
        return $this->_fields;
    }

    public function addField($field) {
        $this->_fields[] = $field;
        return $this;
    }

    public function setOptions($options) {
        $this->_options = $options;
        return $this;
    }

    public function getOption($option) {
        return isset($this->_options[$option]) ? $this->_options[$option] : false;
    }
}