<?php
/**
 * @category    Mana
 * @package     Mana_Db
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method Mage_Core_Model_Resource_Setup getInstaller()
 * @method Mana_Db_Model_Setup_Abstract setInstaller(Mage_Core_Model_Resource_Setup $value)
 * @method string getModuleName()
 * @method Mana_Db_Model_Setup_Abstract setModuleName(string $value)
 * @method string getVersion()
 * @method Mana_Db_Model_Setup_Abstract setVersion(string $value)
 * @method string getSql()
 * @method Mana_Db_Model_Setup_Abstract setSql(string $value)
 */
class Mana_Db_Model_Setup_Abstract extends Varien_Object {
    public function getTable($entityName) {
        /* @var $db Mana_Db_Helper_Data */
        $db = Mage::helper('mana_db');

        return $this->getInstaller()->getTable($db->getScopedName($entityName));
    }
    public function prepare() {
        return $this;
    }

    public function run() {
        return $this;
    }
}