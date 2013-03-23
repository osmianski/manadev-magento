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
class Mana_Db_Helper_Formula_Entity extends Mage_Core_Helper_Abstract {
    protected $_name;

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Entity $entity
     */
    public function select($context, $entity) {
        $mode = $context->getMode() ? $context->getMode() : 'normal';
        call_user_func(array($this, '_select'.ucfirst($mode)), $context, $entity);
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Entity $entity
     */
    public function endSelect($context, $entity) {
        $mode = $context->getMode() ? $context->getMode() : 'normal';
        call_user_func(array($this, '_endSelect' . ucfirst($mode)), $context, $entity);
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Expr $expr
     */
    public function selectField(/** @noinspection PhpUnusedParameterInspection */$context, $expr) {
        throw new Exception('Not implemented');
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Entity $entity
     */
    protected function _selectNormal(/** @noinspection PhpUnusedParameterInspection */$context, $entity) {
        throw new Exception('Not implemented');
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Entity $entity
     */
    protected function _endSelectNormal(/** @noinspection PhpUnusedParameterInspection */$context, $entity) {
        throw new Exception('Not implemented');
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Entity $entity
     */
    protected function _selectAggregate(/** @noinspection PhpUnusedParameterInspection */$context, $entity) {
        throw new Exception('Not implemented');
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Entity $entity
     */
    protected function _endSelectAggregate(/** @noinspection PhpUnusedParameterInspection */$context, $entity) {
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Entity $entity
     */
    protected function _selectFrontend(/** @noinspection PhpUnusedParameterInspection */$context, $entity) {
    }

    /**
     * @param Mana_Db_Model_Formula_Context $context
     * @param Mana_Db_Model_Formula_Entity $entity
     */
    protected function _endSelectFrontend(/** @noinspection PhpUnusedParameterInspection */$context, $entity) {
    }

    public function getName() {
        if (!$this->_name) {
            $class = substr(get_class($this), strlen(__CLASS__ . '_'));
            $this->_name = strtolower($class);
        }

        return $this->_name;
    }

}