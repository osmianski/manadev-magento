<?php
/** 
 * @category    Mana
 * @package     ManaPage_Sale
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPage_Sale_Helper_Special_Promo extends Mana_Page_Helper_Special_Rule {
    protected $_allRuleIds;
    protected $_productIds = array();

    public function join($select, $xml) {
    }

    public function where($xml) {
        $ids = $this->_getProductIds(empty($xml['id']) ? null : (string)$xml['id']);

        if (count($ids)) {
            return "`e`.`entity_id` IN (" . implode(', ', $ids) . ")";
        }
        else {
            return "1 = 0";
        }
    }

    protected function _getAllActiveRuleIds() {
        if (!$this->_allRuleIds) {
            $todayDate = Mage::app()->getLocale()->date()->toString(Varien_Date::DATE_INTERNAL_FORMAT);
            /* @var $res Mage_Core_Model_Resource */
            $res = Mage::getSingleton('core/resource');

            /* @var $db Varien_Db_Adapter_Pdo_Mysql */
            $db = $res->getConnection('read');

            $rules = Mage::getModel('catalogrule/rule')->getResourceCollection()
                    ->addFieldToFilter('is_active', 1)
                    ->addWebsiteFilter(Mage::getModel('core/store')->load(Mage::app()->getStore()->getId())->getWebsiteId())
                    ->addFieldToFilter('from_date', array('or' => array(
                        0 => array('date' => true, 'to' => $todayDate),
                        1 => array('is' => new Zend_Db_Expr('null')))
                    ))
                    ->addFieldToFilter('to_date', array('or' => array(
                        0 => array('date' => true, 'from' => $todayDate),
                        1 => array('is' => new Zend_Db_Expr('null')))
                    ));
            if (Mage::helper('mana_core')->isMageVersionEqualOrGreater('1.7')) {
                $rules->getSelect()
                        ->joinInner(array('cg' => $res->getTableName('catalogrule/customer_group')), $db->quoteInto(
                    'cg.rule_id=main_table.rule_id AND cg.customer_group_id=?',
                    Mage::getSingleton('customer/session')->getCustomerGroupId()), null);
            }
            else {
                $rules->getSelect()
                        ->where(new Zend_Db_Expr($db->quoteInto('FIND_IN_SET(?, main_table.customer_group_ids)',
                    Mage::getSingleton('customer/session')->getCustomerGroupId())));
            }

            $this->_allRuleIds = $rules->getAllIds();
        }

        return $this->_allRuleIds;
    }

    protected function _getProductIds($ruleId = null) {
        $key = $ruleId ? $ruleId : 'all';
        if (!isset($this->_productIds[$key])) {
            if (!$ruleId) {
                $ruleIds = $this->_getAllActiveRuleIds();
            }
            else {
                $ruleIds = array($ruleId);
            }

            /* @var $res Mage_Core_Model_Resource */
            $res = Mage::getSingleton('core/resource');

            /* @var $db Varien_Db_Adapter_Pdo_Mysql */
            $db = $res->getConnection('read');

            $select = $db->select()
                ->from(array('p' => $res->getTableName('catalogrule_product')), 'product_id')
                ->distinct()
                ->where('p.rule_id IN (?)', $ruleIds);

            $this->_productIds[$key] = $db->fetchCol($select);
        }
        return $this->_productIds[$key];
    }
}