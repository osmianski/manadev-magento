<?php

class Local_Manadev_Resource_Composer_Repo extends Mage_Core_Model_Mysql4_Abstract
{
    public function loadByKey($key) {
        $db = $this->getReadConnection();

        return $db->fetchRow($db->select()->from($this->getMainTable())
            ->where('`key`  = ?', $key));
    }

    protected function _construct() {
        $this->_init('local_manadev/composer_repo', 'id');
    }

}