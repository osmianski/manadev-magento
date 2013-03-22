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
 * @method string getAlias()
 * @method Mana_Db_Model_Formula_Context setAlias(string $value)
 */
class Mana_Db_Model_Formula_Context extends Varien_Object {
    /**
     * @var Varien_Db_Select()
     */
    protected $_select;

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

    protected $_aliases = array();

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
    public function getChildContext() {
        /* @var $result Mana_Db_Model_Formula_Context */
        $result = Mage::getModel('mana_db/formula_context');
        return $result
            ->setHelper($this->getHelper())
            ->setParentContext($this);
    }

    /**
     * @return Mana_Db_Model_Formula_Context
     */
    public function getTopContext() {
        for ($result = $this; $result->getParentContext() != null; $result = $result->getParentContext());
        return $result;
    }

    /**
     * @return Mana_Db_Model_Formula_Context
     */
    public function getSelectContext() {
        for ($result = $this->getParentContext(); $result && !$result->getIsAggregate(); $result = $result->getParentContext()) ;

        return $result;
    }

    public function registerAlias($alias) {
        if (!isset($this->_aliases[$alias])) {
            $letter = substr($alias, 0, 1);
            if ($parentContext = $this->getSelectContext()) {
                $letter = $parentContext->registerAlias($parentContext->getAlias()).$letter;
            }
            for ($i = 1, $result = $letter; in_array($result, $this->_aliases); $i++, $result = $letter . $i) ;
            $this->_aliases[$alias] = $result;
        }
        return $this->_aliases[$alias];
    }

    public function resolveAliases($expr) {
        return preg_replace_callback('/{{=(.*)}}/', array($this, '_resolveAlias'), $expr);
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
            $this->getSelectContext()->_resolveAlias(implode('.', array_slice($fieldExpr, 1)));
        }
        elseif (count($fieldExpr) == 2) {
            list($alias, $field) = $fieldExpr;
            return "`{$this->registerAlias($alias)}`.`$field`";
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
}