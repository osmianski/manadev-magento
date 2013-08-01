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
class Mana_Db_Model_Formula_Closure_ForeignJoin extends Mana_Db_Model_Formula_Closure {
    /**
     * @return mixed|void
     */
    public function execute() {
        /* @var $dbConfig Mana_Db_Helper_Config */
        $dbConfig = Mage::helper('mana_db/config');

        $entity = $this->getEntity();
        $context = $this->getContext();
        return "{{= {$entity->getAlias()->asString($this->getIndex())}.{$entity->getProcessor()->getPrimaryKey($entity->getEntity())} }} = " .
            "{{= {$context->getAlias()->asString($this->getIndex())}.{$dbConfig->getForeignKey($entity->getEntity(), $context->getEntity())} }}";
    }
}