<?php
/** 
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method string getEntity()
 * @method Mana_Db_Model_Formula_EvaluationContext setEntity(string $value)
 * @method Mage_Core_Model_Abstract getModel()
 * @method Mana_Db_Model_Formula_EvaluationContext setModel(Mage_Core_Model_Abstract $value)
 * @method Mana_Db_Model_Formula_EntityResolver getEntityResolver()
 * @method Mana_Db_Model_Formula_FieldResolver getFieldResolver()
 */
class Mana_Db_Model_Formula_EvaluationContext extends Varien_Object {
    /**
     * @param Mana_Db_Model_Formula_EntityResolver | string $resolver
     * @return Mana_Db_Model_Formula_EvaluationContext
     */
    public function setEntityResolver($resolver) {
        if (is_string($resolver)) {
            $resolver = Mage::getSingleton('mana_db/formula_entityResolver_'.$resolver);
        }
        $this->setData('entity_resolver', $resolver);
        return $this;
    }

    /**
     * @param Mana_Db_Model_Formula_FieldResolver | string $resolver
     * @return Mana_Db_Model_Formula_EvaluationContext
     */
    public function setFieldResolver($resolver) {
        if (is_string($resolver)) {
            $resolver = Mage::getSingleton('mana_db/formula_fieldResolver_' . $resolver);
        }
        $this->setData('field_resolver', $resolver);

        return $this;
    }
}