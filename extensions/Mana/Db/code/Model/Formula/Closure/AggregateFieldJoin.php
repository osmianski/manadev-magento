<?php
/** 
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method Mana_Db_Model_Formula_Context getContext()
 * @method Mana_Db_Model_Formula_Closure_AggregateFieldJoin setContext(Mana_Db_Model_Formula_Context $value)
 * @method int getTargetIndex()
 * @method Mana_Db_Model_Formula_Closure_AggregateFieldJoin setTargetIndex(int $value)
 * @method Mana_Db_Model_Formula_Entity getResult()
 * @method Mana_Db_Model_Formula_Closure_AggregateFieldJoin setResult(Mana_Db_Model_Formula_Entity $value)
 * @method array getCompositeAlias()
 * @method Mana_Db_Model_Formula_Closure_AggregateFieldJoin setCompositeAlias(array $value)
 * @method array getFields()
 * @method Mana_Db_Model_Formula_Closure_AggregateFieldJoin setFields(array $value)
 * @method string getEntityName()
 * @method Mana_Db_Model_Formula_Closure_AggregateFieldJoin setEntityName(string $value)
 * @method Varien_Simplexml_Element getFieldXml()
 * @method Mana_Db_Model_Formula_Closure_AggregateFieldJoin setFieldXml(Varien_Simplexml_Element $value)
 *
 */
class Mana_Db_Model_Formula_Closure_AggregateFieldJoin extends Mana_Db_Model_Formula_Closure {
    /**
     * @return mixed|void
     */
    public function execute() {
        $context = $this->getContext();
        $fieldXml = $this->getFieldXml();

        $fieldAlias = $this->getAlias()->asString($this->getIndex());

        $compositeAlias = $this->getCompositeAlias() ? $this->getCompositeAlias() : array();
        $compositeAlias[$this->getTargetIndex()] = $fieldAlias;
        $this->setCompositeAlias($compositeAlias);

        $fields = $this->getFields() ? $this->getFields() : array();
        /** @noinspection PhpUndefinedFieldInspection */
        $fields[$this->getTargetIndex()] = array(
            'alias' => $fieldAlias,
            'entity' => (string)$fieldXml->foreign->entity,
            'join' => "{{= {$fieldAlias}.{$this->getResult()->getProcessor()->getPrimaryKey($this->getEntityName())} }} = " .
                "{{= {$context->getAlias()->asString($this->getIndex())}.{$fieldXml->getName()} }}",
        );
        $this->setFields($fields);
    }
}