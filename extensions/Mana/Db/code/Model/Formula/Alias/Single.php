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
class Mana_Db_Model_Formula_Alias_Single extends Mana_Db_Model_Formula_Alias {
    /**
     * @var string
     */
    protected $_alias;
    public function __construct($alias) {
        $this->_alias = $alias;
    }

    public function getAlias() {
        return $this->_alias;
    }

    /**
     * @param Mana_Db_Model_Formula_Alias $suffix
     * @return Mana_Db_Model_Formula_Alias
     */
    public function child($suffix) {
        return $suffix->singleChild($this);
    }

    /**
     * @param Mana_Db_Model_Formula_Alias_Single $prefix
     * @return Mana_Db_Model_Formula_Alias
     */
    public function singleChild($prefix) {
        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');

        return $formulaHelper->createAlias($prefix->getAlias().'.'.$this->getAlias());
    }

    /**
     * @param Mana_Db_Model_Formula_Alias_Array $prefix
     * @return Mana_Db_Model_Formula_Alias
     */
    public function arrayChild($prefix) {
        /* @var $formulaHelper Mana_Db_Helper_Formula */
        $formulaHelper = Mage::helper('mana_db/formula');

        $aliases = array();
        foreach ($prefix->getAliases() as $index => $alias) {
            $aliases[$index] = $alias . '.' . $this->getAlias();
        }
        return $formulaHelper->createAlias($aliases);
    }

    /**
     * @param Mana_Db_Model_Formula_Closure $closure
     * @return mixed
     */
    public function each($closure) {
        return $closure
            ->setIndex(null)
            ->setAlias($this)
            ->execute();
    }

    /**
     * @param mixed $index
     * @return string
     */
    public function asString($index) {
        return $this->getAlias();
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param string $expr
     * @return string | string[]
     */
    public function expr($context, $expr) {
        return $context->resolveAliases($expr);
    }
}