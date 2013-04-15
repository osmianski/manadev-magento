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
 * @method Mana_Db_Model_Formula_Closure_ForeignJoin setContext(Mana_Db_Model_Formula_Context $value)
 * @method Mana_Db_Model_Formula_Entity getEntity()
 * @method Mana_Db_Model_Formula_Closure_ForeignJoin setEntity(Mana_Db_Model_Formula_Entity $value)
 */
class Mana_Db_Model_Formula_Closure_ForeignJoinEnd extends Mana_Db_Model_Formula_Closure {
    /**
     * @return mixed|void
     */
    public function execute() {
        /* @var $resource Mana_Db_Resource_Formula */
        $resource = Mage::getResourceSingleton('mana_db/formula');

        /* @var $dbHelper Mana_Db_Helper_Data */
        $dbHelper = Mage::helper('mana_db');

        $entity = $this->getEntity();
        $context = $this->getContext();

        $context->getSelect()->joinLeft(
            array(
                $context->registerAlias($entity->getAlias()->asString($this->getIndex())) =>
                $resource->getTable($dbHelper->getScopedName($entity->getEntity()))
            ),
            $context->resolveAliases($entity->getForeignJoin()),
            null
        );
    }
}