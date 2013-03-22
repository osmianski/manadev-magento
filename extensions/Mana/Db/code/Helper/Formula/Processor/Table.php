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
class Mana_Db_Helper_Formula_Processor_Table extends Mana_Db_Helper_Formula_Processor {
    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param string $field
     * @return Mana_Db_Model_Formula_TypedExpr | bool
     */
    public function selectField($context, $field) {
        if ($result = parent::selectField($context, $field)) {
            return $result;
        }

        /* @var $resource Mana_Db_Resource_Formula */
        $resource = Mage::getResourceSingleton('mana_db/formula');

        $fields = $resource->getTableFields($context->getEntity());

        if (isset($fields[$field])) {
            if (!($alias = $context->getAlias()) || $alias == 'this') {
                $alias = 'primary';
            }

            return $context->getHelper()->expr()
                ->setExpr("`$alias`.`$field`")
                ->setType($fields[$field]['DATA_TYPE'])
                ->setIsAggregate($context->getIsAggregate());
        }
        else {
            return false;
        }
    }
}