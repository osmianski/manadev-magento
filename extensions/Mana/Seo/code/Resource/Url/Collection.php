<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Seo_Resource_Url_Collection extends Mana_Db_Resource_Entity_Collection {
    public function addOptionAttributeIdAndCodeToSelect() {
        $this->getSelect()
            ->joinLeft(array('o' => $this->getTable('eav/attribute_option')),
                "`o`.`option_id` = `main_table`.`option_id`",
                array('option_attribute_id' => new Zend_Db_Expr('`o`.`attribute_id`')))
            ->joinLeft(array('oa' => $this->getTable('eav/attribute')),
                "`oa`.`attribute_id` = `o`.`attribute_id`",
                array('option_attribute_code' => new Zend_Db_Expr('`oa`.`attribute_code`')));
        return $this;
    }
}