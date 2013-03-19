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
class Mana_Db_Model_Formula_FieldResolver_Entity extends Mana_Db_Model_Formula_FieldResolver {
    /**
     * @param Mana_Db_Model_Formula_EvaluationContext $context
     * @param string $field
     * @param mixed $result
     * @return bool
     */
    public function evaluate($context, $field, &$result) {
        /* @var $dbConfig Mana_Db_Helper_Config */
        $dbConfig = Mage::helper('mana_db/config');

        $entityXml = $dbConfig->getScopeXml($context->getEntity());

        /** @noinspection PhpUndefinedFieldInspection */
        if (isset($entityXml->fields->$field)) {
            $result = $context->getModel()->getDataUsingMethod($field);
            return true;
        }
        else {
            return false;
        }
    }
}