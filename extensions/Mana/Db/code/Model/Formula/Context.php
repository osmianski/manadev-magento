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
 * @method Mana_Db_Model_Formula_Alias getAlias()
 * @method Mana_Db_Model_Formula_Context setAlias(Mana_Db_Model_Formula_Alias $value)
 * @method string __getGlobalEntity()
 * @method Mana_Db_Model_Formula_Context __setGlobalEntity(string $value)
 * @method Mana_Db_Helper_Formula_Entity getEntityHelper()
 * @method Mana_Db_Model_Formula_Context setEntityHelper(Mana_Db_Helper_Formula_Entity $value)
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
    protected $_localAliases = array();

    protected $_options = array();

    static protected $_quoteFieldsAndEntities = true;
    static protected $_aliasIndex = null;

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

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @return Mana_Db_Model_Formula_Context
     */
    public function copyAliases($context) {
        $this->_aliases = $context->_aliases;
        return $this;
    }

    public function registerAlias($alias) {
        if (!isset($this->_aliases[$alias])) {
            $relativeAlias = explode('.', $alias);
            $relativeAlias = array_pop($relativeAlias);
            $letter = $this->getPrefix().substr($relativeAlias, 0, 1);
            for ($i = 1, $result = $letter; in_array($result, $this->_aliases); $i++, $result = $letter . $i) ;
            $this->_aliases[$alias] = $result;
        }
        return $this->_aliases[$alias];
    }

    public function resolveAliases($expr, $quoteFieldsAndEntities = true, $aliasIndex = null) {
        self::$_quoteFieldsAndEntities = $quoteFieldsAndEntities;
        self::$_aliasIndex = $aliasIndex;
        return preg_replace_callback('/{{=([^}]*)}}/', array($this, '_resolveAlias'), $expr);
    }

    public function hasAlias($alias) {
        return isset($this->_aliases[$alias]);
    }


    public function resetLocalAliases() {
        $this->_localAliases = array();

        return $this;
    }

    public function addLocalAlias($localAlias, $globalAlias) {
        $this->_localAliases[$localAlias] = $globalAlias;
        return $this;
    }

    protected function _resolveAlias($matches) {
        return $this->_doResolveAlias($matches[1]);
    }

    public function resolveAlias($fieldExpr, $quoteFieldsAndEntities = true, $aliasIndex = null) {
        self::$_quoteFieldsAndEntities = $quoteFieldsAndEntities;
        self::$_aliasIndex = $aliasIndex;
        return $this->_doResolveAlias($fieldExpr);
    }
    protected function _doResolveAlias($fieldExpr) {
        $fieldExpr = explode('.', $fieldExpr);
        foreach ($fieldExpr as $index => $field) {
            $fieldExpr[$index] = trim($field);
        }
        if ($fieldExpr[0] == 'parent') {
            return $this->getParentContext()->_doResolveAlias(implode('.', array_slice($fieldExpr, 1)));
        }
        else {
            $field = array_pop($fieldExpr);
            $alias = implode('.', $fieldExpr);
            if (!$alias) {
                $alias = 'primary';
            }

            if ($fieldExpr[0] == 'context') {
                /* @var $formulaHelper Mana_Db_Helper_Formula */
                $formulaHelper = Mage::helper('mana_db/formula');

                $alias = $this->getAlias()->child($formulaHelper->createAlias(implode('.', array_slice($fieldExpr, 1))));
            }
            elseif (isset($this->_localAliases[$alias])) {
                $alias = $this->_localAliases[$alias];
            }

            if (!is_string($alias)) {
                $alias = $alias->asString(self::$_aliasIndex);
            }

            if (self::$_quoteFieldsAndEntities) {
                return "`{$this->registerAlias($alias)}`.`$field`";
            }
            else {
                return "{$this->registerAlias($alias)}.$field";
            }
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
        $alias = $this->getAlias() ? $this->getAlias() : 'primary';
        $prefix = $this->getPrefix() . $this->registerAlias($alias);
        $this->setPrefix($prefix);
        return $prefix;
    }

    public function decrementPrefix() {
        $alias = $this->getAlias() ? $this->getAlias() : 'primary';
        $prefix = substr($this->getPrefix(), 0, strlen($this->getPrefix()) - strlen($this->registerAlias($alias)));
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