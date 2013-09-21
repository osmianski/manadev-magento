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
abstract class Mana_Db_Helper_Formula_Entity extends Mage_Core_Helper_Abstract {
    protected $_name;

    /**
     *
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Entity $entity
     */
    public abstract function select($context, $entity);

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Entity $entity
     */
    public function endSelect($context, $entity) {
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Node_Field $formula
     * @param Mana_Db_Model_Formula_Expr $expr
     */
    public abstract function selectField($context, $formula, $expr);

    public function getName() {
        if (!$this->_name) {
            $class = substr(get_class($this), strlen(__CLASS__ . '_'));
            $this->_name = strtolower($class);
        }

        return $this->_name;
    }

}