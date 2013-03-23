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
class Mana_Db_Helper_Formula_Entity_Frontend extends Mana_Db_Helper_Formula_Entity {
    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Entity $entity
     * @return Mana_Db_Helper_Formula_Entity
     */
    protected function _selectNormal($context, $entity) {
        $context->setMode($this->getName());

        $context
            ->setEntity($entity->getEntity())
            ->setProcessor($entity->getProcessor())
            ->setAlias($entity->getAlias());
    }
}