<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 */
class Mana_Admin_Block_Data_Entity extends Mana_Admin_Block_Data {
    protected $_additionalEntities = array();
    protected $_model;
    protected $_additionalModels;

    /**
     * @return Mana_Db_Model_Entity
     */
    public function loadModel() {
        if (!$this->_model) {
            /* @var $db Mana_Db_Helper_Data */
            $db = Mage::helper('mana_db');
            $this->_model = $db->getModel($this->getEntity());

            if ($id = Mage::app()->getRequest()->getParam('id')) {
                $this->_model->load($id);
            }
        }

        return $this->_model;
    }

    public function addEntity($name, $entity) {
        $this->_additionalEntities[$name] = $entity;;
        return $this;
    }

    /**
     * @return Mana_Db_Model_Entity[]
     */
    public function loadAdditionalModels() {
        if (!$this->_additionalModels) {
            /* @var $db Mana_Db_Helper_Data */
            $db = Mage::helper('mana_db');

            /* @var $dbConfig Mana_Db_Helper_Config */
            $dbConfig = Mage::helper('mana_db/config');

            $this->_additionalModels = array();
            foreach ($this->_additionalEntities as $key => $entity) {
                $model = $db->getModel($entity);

                $foreignKey = $dbConfig->getForeignKey($this->getEntity(), $entity);
                if ($id = Mage::app()->getRequest()->getParam('id')) {
                    $model->load($id, $foreignKey);
                }
                elseif ($id = $this->loadModel()->getId()) {
                    $model->setData($foreignKey, $id);
                }
                $this->_additionalModels[$key] = $model;
            }
        }

        return $this->_additionalModels;
    }

    public function getLabel($entity, $field) {
        throw new Exception('not implemented');
    }
}