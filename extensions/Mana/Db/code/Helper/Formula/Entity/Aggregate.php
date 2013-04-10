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
class Mana_Db_Helper_Formula_Entity_Aggregate extends Mana_Db_Helper_Formula_Entity {
    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Entity $entity
     */
    public function select($context, $entity) {
        switch ($context->getMode()) {
            default:
                $context
                    ->setMode($this->getName())
                    ->setEntityHelper($this);

                /* @var $dbHelper Mana_Db_Helper_Data */
                $dbHelper = Mage::helper('mana_db');

                /* @var $resource Mana_Db_Resource_Formula */
                $resource = Mage::getResourceSingleton('mana_db/formula');

                $context->incrementPrefix();

                $alias = explode('.', $entity->getAlias());
                $alias = array_pop($alias);

                $aggregateContext = $context->createChildContext()
                    ->setPrefix($context->getPrefix())
                    ->setEntity($entity->getEntity())
                    ->setProcessor($entity->getProcessor())
                    ->setAlias($alias);

                $select = $aggregateContext->getSelect()
                    ->from(array(
                        $aggregateContext->registerAlias($alias)
                        => $resource->getTable($dbHelper->getScopedName($entity->getEntity()))
                    ), null);

                if ($entity->getJoin()) {
                    foreach ($entity->getJoin() as $alias => $join) {
                        $method = isset($join['type']) ? 'join' . ucfirst($join['type']) : 'joinInner';
                        $select->$method(
                            array(
                                $aggregateContext->registerAlias($alias) =>
                                $resource->getTable($dbHelper->getScopedName($join['entity']))
                            ),
                            $aggregateContext->resolveAliases($join['on']),
                            null
                        );
                    }
                }
                if ($entity->getOrder()) {
                    $select->order($aggregateContext->resolveAliases($entity->getOrder(), false));
                }
                if ($entity->getWhere()) {
                    $select->where($aggregateContext->resolveAliases($entity->getWhere()));
                }

                $context
                    ->setEntity($entity->getEntity())
                    ->setProcessor($entity->getProcessor())
                    ->setAlias($entity->getAlias())
                    ->setAggregateContext($aggregateContext);
                break;
        }
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node_Field $formula
     * @param Mana_Db_Model_Formula_Expr $expr
     */
    public function selectField($context, $formula, $expr) {
        $expr
            ->setIsAggregate(true)
            ->setSubSelect($context->getAggregateContext()->getSelect());
    }

}