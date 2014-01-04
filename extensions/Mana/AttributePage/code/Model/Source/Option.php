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
class Mana_AttributePage_Model_Source_Option extends Mana_Core_Model_Source_Abstract {
    protected $_attributeId;

    public function setAttributeId($value) {
        $this->_attributeId = $value;
        return $this;
    }

    protected function _getAllOptions() {
        $res = Mage::getSingleton('core/resource');
        /* @var $db Varien_Db_Adapter_Pdo_Mysql */
        $db = $res->getConnection('read');

        $select = $db->select();

        $select
            ->from(array('o' => $res->getTableName('eav/attribute_option')), null)
            ->joinLeft(array('vg' => $res->getTableName('eav/attribute_option_value')),
                "`vg`.`option_id` = `o`.`option_id` AND `vg`.`store_id` = 0", null);

        if ($this->adminHelper()->isGlobal()) {
            $labelExpr = "`vg`.`value`";
        }
        else {
            $storeId = $this->adminHelper()->getStore()->getId();
            $select->joinLeft(array('vs' => $res->getTableName('eav/attribute_option_value')),
                $db->quoteInto("`vs`.`option_id` = `o`.`option_id` AND `vs`.`store_id` = ?", $storeId), null);
            $labelExpr = "COALESCE(`vs`.`value`, `vg`.`value`)";
        }

        $labelExpr = new Zend_Db_Expr($labelExpr);
        $select
            ->columns(array('option_id' => 'o.option_id', 'label' => $labelExpr))
            ->where("`o`.`attribute_id` = ?", $this->_attributeId)
            ->order("label ASC");

        $sql = $select->__toString();
        $result = array();
        foreach ($db->fetchPairs($select) as $value => $label) {
            $result[] = array('value' => $value, 'label' => $label);
        }

        return $result;
    }

    #region Dependencies
    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }
    #endregion
}