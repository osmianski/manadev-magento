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
abstract class Mana_Db_Model_Formula_FieldResolver  {
    /**
     * @param Mana_Db_Model_Formula_EvaluationContext $context
     * @param string $field
     * @param mixed $result
     * @return bool
     */
    abstract public function evaluate($context, $field, &$result);
}