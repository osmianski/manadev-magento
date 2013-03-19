<?php
/** 
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method string getName()
 * @method Mana_Db_Model_Formula_Entity setName(string $value)
 * @method int getSortOrder()
 * @method Mana_Db_Model_Formula_Entity setSortOrder(int $value)
 * @method string getEntity()
 * @method Mana_Db_Model_Formula_Entity setEntity(string $value)
 */
class Mana_Db_Model_Formula_Entity extends Varien_Object {
    public function getXml() {
        /* @var $dbConfig Mana_Db_Helper_Config */
        $dbConfig = Mage::helper('mana_db/config');

        return $dbConfig->getScopeXml($this->getEntity());
    }

    public function getTableName() {
        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        /* @var $res Mage_Core_Model_Resource */
        $res = Mage::getSingleton('core/resource');

        return $res->getTableName($db->getScopedName($this->getEntity()));
    }
}