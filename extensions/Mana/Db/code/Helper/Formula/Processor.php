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
abstract class Mana_Db_Helper_Formula_Processor extends Mage_Core_Helper_Abstract {
    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param string $field
     * @return Mana_Db_Model_Formula_TypedExpr | bool
     */
    public function selectField(/** @noinspection PhpUnusedParameterInspection */$context, $field) {
        return false;
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param string $entity
     * @return Mana_Db_Model_Formula_Context | bool
     */
    public function selectEntity(/** @noinspection PhpUnusedParameterInspection */$context, $entity) {
        return false;
    }

    public function getProcessor($entity) {
        /* @var $dbConfig Mana_Db_Helper_Config */
        $dbConfig = Mage::helper('mana_db/config');

        if ($dbConfig->getScopeXml($entity)) {
            return 'entity';
        }
        else {
            return 'table';
        }
    }

}