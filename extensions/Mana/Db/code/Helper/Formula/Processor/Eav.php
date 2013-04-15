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
class Mana_Db_Helper_Formula_Processor_Eav extends Mana_Db_Helper_Formula_Processor {
    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param string $field
     * @return Mana_Db_Model_Formula_Expr | bool
     */
    public function selectField($context, $field) {
        if ($result = parent::selectField($context, $field)) {
            return $result;
        }

        $eavEntityType = $this->getEavEntityType($context->getEntity());
        /* @var $attribute Mage_Eav_Model_Entity_Attribute */
        $attribute = $eavEntityType->getAttributeCollection()->getItemByColumnValue('attribute_code', $field);

        if ($attribute) {
            return $context->getHelper()->expr()
                ->setFieldExpr($context->getAlias()->fieldExpr($context, $field))
                ->setFieldName($field)
                ->setType($attribute->getBackendType());
        }
        else {
            return false;
        }
    }

    public function getPrimaryKey($entity) {
        return 'entity_id';
    }
}