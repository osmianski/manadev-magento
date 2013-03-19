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
class Mana_Db_Model_Formula_Engine  {
    /**
     * @var Mana_Db_Model_Formula_EntityConfig[]
     */
    protected $_configs = array();

    /**
     * @param string[] $formulas
     */
    public function parseFormulas($formulas) {
        $result = array();
        foreach ($formulas as $key => $formula) {
            $result[$key]  = $this->parseFormula($formula);
        }

    }

    public function parseFormula($formula) {
        /* @var $parser Mana_Db_Model_Formula_Parser */
        $parser = Mage::getModel('mana_db/formula_parser');
        return $parser->parseText($formula);
    }

    /**
     * @param Mana_Db_Model_Entity $model
     * @return Mana_Db_Model_Formula_Engine
     */
    public function evaluateFrontendFormulas($model) {
        /* @var $context Mana_Db_Model_Formula_EvaluationContext */
        $context = Mage::getModel('mana_db/formula_evaluationContext');
        $context
            ->setEntity($model->getScope())
            ->setModel($model)
            ->setEntityResolver('entity')
            ->setFieldResolver('entity');

        /* @var $evaluation Mana_Db_Model_Formula_Evaluation */
        $evaluation = Mage::getModel('mana_db/formula_evaluation');
        $evaluation
            ->setContext($context)
            ->setFieldConfig($this->getFieldConfig($model->getScope(), $model->getDefaultFormulas()))
            ->evaluateModel();

        return $this;
    }

    /**
     * @param string $entity
     * @param array $data
     */
    public function evaluateWhileEditing($entity, $data) {
        throw new Exception('Not implemented');
    }

    /**
     * @param string $entity
     * @param $formulas
     * @param array $options
     * @return Mana_Db_Model_Formula_Evaluation
     */
    public function evaluateWhileIndexing($entity, $formulas, $options = array()) {
        throw new Exception('Not implemented');
    }

    public function getEntityConfig($entity) {
        if (!isset($this->_configs[$entity])) {
            /* @var $core Mana_Core_Helper_Data */
            $core = Mage::helper('mana_core');
            /* @var $db Mana_Db_Helper_Data */
            $db = Mage::helper('mana_db');
            /* @var $dbConfig Mana_Db_Helper_Config */
            $dbConfig = Mage::helper('mana_db/config');

            /* @var $config Mana_Db_Model_Formula_EntityConfig */
            $config = Mage::getModel('mana_db/formula_entityConfig');
            $config->setFlat($entity);
            if (!isset($config->getFlat()->getXml()->flattens)) {
                throw new Exception($db->__("Entity '%s' is expected to flatten other entity
                ; it doesn't.", $config->getFlat()->getEntity()));
            }
            $config->setPrimary((string)$config->getFlat()->getXml()->flattens);

            foreach ($dbConfig->getForeignXmls($config->getPrimary()->getEntity()) as $fieldXml) {
                if (!isset($fieldXml->foreign->formula)) {
                    continue;
                }

                $name = $fieldXml->getName();
                if (isset($fieldXml->foreign->formula->name)) {
                    $name = (string)$fieldXml->foreign->formula->name;
                }
                elseif ($core->endsWith($name, '_id')) {
                    $name = substr($name, 0, strlen($name) - strlen('_id'));
                }

                $config->addForeign($name, (string)$fieldXml->foreign->entity);
            }

            if (isset($config->getPrimary()->getXml()->store_specifics_for)) {
                $config->addForeign('global', (string)$config->getPrimary()->getXml()->store_specifics_for);
                $config->addForeign('store', 'core/store', 'store');
            }
            $this->_configs[$entity] = $config;
        }

        return $this->_configs[$entity];
    }

    /**
     * @param string $entity
     * @param string | string[] $formulas
     * @return Mana_Db_Model_Formula_FieldConfig
     */
    public function getFieldConfig($entity, $formulas) {
        if (!$formulas) {
            $formulas = array();
        }
        if (is_string($formulas)) {
            $formulas = json_decode($formulas, true);
        }

        $entityConfig = $this->getEntityConfig($entity);
        /* @var $fieldConfig Mana_Db_Model_Formula_FieldConfig */
        $fieldConfig = Mage::getModel('mana_db/formula_fieldConfig');
        foreach ($entityConfig->getPrimary()->getXml()->fields->children() as $fieldXml) {
        }

        return $fieldConfig;
    }
}