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
class ManaPage_Sale_Block_Filter extends Mana_Page_Block_Filter {
    public function prepareProductCollection() {
        /////////// Begin Special Price and Rule Filtering
        $todayDate = $this->getTodayDate();
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
        } else {
            $rules->getSelect()
                    ->where(new Zend_Db_Expr($db->quoteInto('FIND_IN_SET(?, main_table.customer_group_ids)',
                Mage::getSingleton('customer/session')->getCustomerGroupId())));
        }
        $promoids = array();
        if (!$this->getIgnorePromos()) {
            $ruleIds = $rules->getAllIds();
            if (count($ruleIds)) {
                $select = $db->select()
                    ->from(array('p' => $res->getTableName('catalogrule_product')), 'product_id')
                    ->distinct()
                    ->where('p.rule_id IN (?)', $ruleIds);
                $promoids = array_merge($promoids, $db->fetchCol($select));
            }
        }
        $specialids = array();
        if (!$this->getIgnoreSpecials()) {
            $_specialProducts = Mage::getResourceModel('catalog/product_collection');
            $_specialProducts->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());
            $_specialProducts = $this->_addProductAttributesAndPrices($_specialProducts)
                    ->addStoreFilter()
                    ->addAttributeToFilter('special_from_date', array('date' => true, 'to' => $todayDate))
                    ->addAttributeToFilter('special_to_date', array('or' => array(
                0 => array('date' => true, 'from' => $todayDate),
                1 => array('is' => new Zend_Db_Expr('null')))
            ), 'left');
            $specialids = $_specialProducts->getAllIds();
        }
        $merged_ids = array_merge($specialids, $promoids);

        $this->_condition = count($merged_ids)
            ? $db->quoteInto('`e`.`entity_id` IN (?)', $merged_ids)
            : '`e`.`entity_id` = 0';

        return $this;
    }
}