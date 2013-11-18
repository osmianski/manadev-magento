<?php
/** 
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_AttributePage_Resource_OptionPage_Store_Collection extends Mana_AttributePage_Resource_OptionPage_Abstract_Collection {
    protected function _construct() {
        $this->_init(Mana_AttributePage_Model_OptionPage_Store::ENTITY);
    }

    /**
     * @param $attributePageGlobalId
     * @return $this
     */
    public function addAttributePageFilter($attributePageGlobalId) {
        $db = $this->getConnection();
        $this->getSelect()
            ->joinInner(array('op_g' => $this->getTable('mana_attributepage/optionPage_global')),
                "`op_g`.`id` = `main_table`.`option_page_global_id` AND ".
                $db->quoteInto("`op_g`.`attribute_page_global_id` = ?", $attributePageGlobalId), null);
        return $this;
    }

    /**
     * @param $storeId
     * @return $this
     */
    public function addStoreFilter($storeId) {
        $this->getSelect()
            ->where("`main_table`.`store_id` = ?", $storeId);
        return $this;
    }
}