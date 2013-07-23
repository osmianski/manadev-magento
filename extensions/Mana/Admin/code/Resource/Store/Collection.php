<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Admin_Resource_Store_Collection extends Mage_Core_Model_Mysql4_Store_Collection {
    protected function _beforeLoad() {
        parent::_beforeLoad();
        $this->getSelect()
            ->joinLeft(array('store_group' => $this->getTable('core/store_group')),
                'main_table.group_id=store_group.group_id', null)
            ->joinLeft(array('website' => $this->getTable('core/website')),
                'store_group.website_id=website.website_id', null)
            ->columns(array('hierarchy' => new Zend_Db_expr(
                "CONCAT(`website`.`name`, ' -> ', `store_group`.`name`, ' -> ', `main_table`.`name`)")));
        return $this;
    }

    public function toOptionHash() {
        return $this->_toOptionHash('store_id', 'hierarchy');
    }
}