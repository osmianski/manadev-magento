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
class Mana_Db_Model_Formula_Alias_Empty extends Mana_Db_Model_Formula_Alias {
    /**
     * @param Mana_Db_Model_Formula_Alias $suffix
     * @return Mana_Db_Model_Formula_Alias
     */
    public function child($suffix) {
        return $suffix;
    }

    /**
     * @param Mana_Db_Model_Formula_Alias_Single $prefix
     * @throws Exception
     * @return Mana_Db_Model_Formula_Alias
     */
    public function singleChild($prefix) {
        return $prefix;
    }

    /**
     * @param Mana_Db_Model_Formula_Alias_Array $prefix
     * @throws Exception
     * @return Mana_Db_Model_Formula_Alias
     */
    public function arrayChild($prefix) {
        return $prefix;
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
     * @throws Exception
     * @return string
     */
    public function asString($index) {
        return 'primary';
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param string $expr
     * @throws Exception
     * @return string | string[]
     */
    public function expr($context, $expr) {
        return $context->resolveAliases($expr);
    }
}