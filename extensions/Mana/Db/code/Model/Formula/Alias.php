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
abstract class Mana_Db_Model_Formula_Alias  {
    /**
     * @param Mana_Db_Model_Formula_Alias $suffix
     * @return Mana_Db_Model_Formula_Alias
     */
    abstract public function child($suffix);

    /**
     * @param Mana_Db_Model_Formula_Alias_Single $prefix
     * @return Mana_Db_Model_Formula_Alias
     */
    abstract public function singleChild($prefix);

    /**
     * @param Mana_Db_Model_Formula_Alias_Array $prefix
     * @return Mana_Db_Model_Formula_Alias
     */
    abstract public function arrayChild($prefix);

    /**
     * @param Mana_Db_Model_Formula_Closure $closure
     * @return mixed
     */
    abstract public function each($closure);

    /**
     * @param mixed $index
     * @return string
     */
    abstract public function asString($index);

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param string $field
     * @return string | string[]
     */
    public function fieldExpr($context, $field) {
        return $this->expr($context, "{{= context.$field }}");
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param string $expr
     * @return string | string[]
     */
    public abstract function expr($context, $expr);
}