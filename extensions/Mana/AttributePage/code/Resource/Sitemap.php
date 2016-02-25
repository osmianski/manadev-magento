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
class Mana_AttributePage_Resource_Sitemap extends Mage_Core_Model_Mysql4_Abstract {
    /**
     * @param $storeId
     * @return string[]
     * @throws Zend_Db_Select_Exception
     */
    public function getAttributePageUrls($storeId) {
        $db = $this->getReadConnection();
        $schema = $this->seoHelper()->getActiveSchema($storeId);
        $select = $db->select()
            ->from(array('ap' => $this->getTable('mana_attributepage/attributePage_store')), null)
            ->joinInner(array('url' => $this->getTable('mana_seo/url')),
                "`url`.`attribute_page_id` = `ap`.`id` AND `url`.`status` = 'active' AND " .
                $db->quoteInto("`url`.`type` = ? AND", 'attribute_page') .
                $db->quoteInto("`url`.`schema_id` = ?", $schema->getId()), null)
            ->where("`ap`.`store_id` = ?", $storeId)
            ->where("`ap`.`is_active` = 1")
            ->columns(new Zend_Db_Expr($db->quoteInto("CONCAT(`url`.`final_url_key`, ?)",
                Mage::getStoreConfig('mana_attributepage/seo/attribute_page_url_suffix', $storeId))));

        return $db->fetchCol($select);
    }

    public function getOptionPageUrls($storeId) {
        $db = $this->getReadConnection();
        $schema = $this->seoHelper()->getActiveSchema($storeId);
        $select = $db->select()
            ->from(array('op' => $this->getTable('mana_attributepage/optionPage_store')), null)
            ->joinInner(array('op_g' => $this->getTable('mana_attributepage/optionPage_global')), "`op_g`.`id` = `op`.`option_page_global_id`", null)
            ->joinInner(array('ap' => $this->getTable('mana_attributepage/attributePage_store')),
                $db->quoteInto("`ap`.`attribute_page_global_id` = `op_g`.`attribute_page_global_id` AND `ap`.`store_id` = ? AND `ap`.`is_active` = 1", $storeId), null)
            ->joinInner(array('url' => $this->getTable('mana_seo/url')),
                "`url`.`option_page_id` = `op`.`id` AND `url`.`status` = 'active' AND " .
                $db->quoteInto("`url`.`type` = ? AND", 'option_page') .
                $db->quoteInto("`url`.`schema_id` = ?", $schema->getId()), null)
            ->where("`op`.`store_id` = ?", $storeId)
            ->where("`op`.`is_active` = 1")
            ->columns(new Zend_Db_Expr($db->quoteInto("CONCAT(`url`.`final_url_key`, ?)",
                Mage::getStoreConfig('mana_attributepage/seo/option_page_url_suffix', $storeId))));

        return $db->fetchCol($select);
    }

    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_setResource('mana_attributepage');
    }

    #region Dependencies

    /**
     * @return Mana_Seo_Helper_Data
     */
    public function seoHelper() {
        return Mage::helper('mana_seo');
    }

    #endregion
}