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
class Mana_Db_Model_Formula_EntityResolver_Entity extends Mana_Db_Model_Formula_EntityResolver {
    /**
     * @param Mana_Db_Model_Formula_EvaluationContext $context
     * @param string $entity
     * @return Mana_Db_Model_Formula_EvaluationContext | null
     */
    public function evaluate($context, $entity) {
        /* @var $engine Mana_Db_Model_Formula_Engine */
        $engine = Mage::getSingleton('mana_db/formula_engine');
        $config = $engine->getEntityConfig($context->getEntity());

        if ($foreign = $config->getForeign($entity)) {
            $result = clone $context;
            $result
                ->setEntity($entity);
            return $result;
        }
        else {
            return null;
        }
    }
}