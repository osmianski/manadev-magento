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
 * @method Mana_Db_Model_Formula_Closure_Join setContext(Mana_Db_Model_Formula_Context $value)
 * @method Varien_Simplexml_Element getDefinition()
 * @method Mana_Db_Model_Formula_Closure_Join setDefinitionVarien_Simplexml_Element $value)
 */
class Mana_Db_Model_Formula_Closure_Join extends Mana_Db_Model_Formula_Closure {
    /**
     * @return mixed|void
     */
    public function execute() {
        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        /* @var $resource Mana_Db_Resource_Formula */
        $resource = Mage::getResourceSingleton('mana_db/formula');

        $definition = $this->getDefinition();
        $alias = $definition->getName();
        $fullAlias = $this->getAlias()->asString($this->getIndex());

        $this->getContext()->addLocalAlias($alias, $this->getAlias());
        if (!$this->getContext()->hasAlias($fullAlias)) {
            /** @noinspection PhpUndefinedFieldInspection */
            $entity = $alias == 'primary' ? $this->getContext()->getPrimaryEntity() : (string)$definition->entity;
            $method = isset($definition->type) ? 'join' . ucfirst($definition->type) : 'joinInner';

            /** @noinspection PhpUndefinedFieldInspection */
            $this->getContext()->getSelect()->$method(
                array($this->getContext()->registerAlias($fullAlias) => $resource->getTable($dbHelper->getScopedName($entity))),
                $this->getContext()->resolveAliases((string)$definition->on, true, $this->getIndex()),
                null
            );
        }
    }
}