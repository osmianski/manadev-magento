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
class Mana_Db_Model_Formula_Alias_Array extends Mana_Db_Model_Formula_Alias {
    /**
     * @var string[]
     */
    protected $_aliases;

    public function __construct($aliases) {
        $this->_aliases = $aliases;
    }

    public function getAliases() {
        return $this->_aliases;
    }

    /**
     * @param Mana_Db_Model_Formula_Alias $suffix
     * @return Mana_Db_Model_Formula_Alias
     */
    public function child($suffix) {
        return $suffix->arrayChild($this);
    }

    /**
     * @param Mana_Db_Model_Formula_Alias_Single $prefix
     * @return Mana_Db_Model_Formula_Alias
     */
    public function singleChild($prefix) {
        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');

        $aliases = array();
        foreach ($this->getAliases() as $index => $alias) {
            $aliases[$index] = $prefix->getAlias() . '.' . $alias;
        }

        return $formulaHelper->createAlias($aliases);
    }

    /**
     * @param Mana_Db_Model_Formula_Alias_Array $prefix
     * @return Mana_Db_Model_Formula_Alias
     */
    public function arrayChild($prefix) {
        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');

        $aliases = array();
        foreach ($this->getAliases() as $thisIndex => $thisAlias) {
            foreach ($prefix->getAliases() as $prefixIndex => $prefixAlias) {
                $aliases["$prefixIndex.$thisIndex"] = $prefixAlias . '.' . $thisAlias;
            }
        }

        return $formulaHelper->createAlias($aliases);
    }

    /**
     * @param Mana_Db_Model_Formula_Closure $closure
     * @return mixed
     */
    public function each($closure) {
        $closure->setAlias($this);
        $result = array();
        foreach ($this->getAliases() as $index => $alias) {
            $result[$index] = $closure
                ->setIndex($index)
                ->execute();
        }
        return $result;
    }

    /**
     * @param mixed $index
     * @return string
     */
    public function asString($index) {
        return $this->_aliases[$index];
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param string $expr
     * @return string | string[]
     */
    public function expr($context, $expr) {
        $result = array();
        foreach ($this->getAliases() as $index => $alias) {
            $result[$index] = $context->resolveAliases($expr, true, $index);
        }
        return $result;
    }

}