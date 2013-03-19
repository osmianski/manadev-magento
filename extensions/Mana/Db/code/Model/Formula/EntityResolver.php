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
abstract class Mana_Db_Model_Formula_EntityResolver  {
    /**
     * @param Mana_Db_Model_Formula_EvaluationContext $context
     * @param string $entity
     * @return Mana_Db_Model_Formula_EvaluationContext | null
     */
    abstract public function evaluate($context, $entity);
}