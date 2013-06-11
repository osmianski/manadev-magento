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
class Mana_Seo_Resource_UrlIndexer_General extends Mage_Core_Model_Mysql4_Abstract {
    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_setResource('core');
    }

    public function makeAllRowsObsolete($options) {
        $db = $this->_getWriteAdapter();

        $db->query("UPDATE `{$this->getTable('mana_seo/url')}` SET status = ? WHERE status = ?", array(
            Mana_Seo_Model_Url::STATUS_OBSOLETE,
            Mana_Seo_Model_Url::STATUS_ACTIVE
        ));
    }

    public function calculateFinalFields($options) {
        $db = $this->_getWriteAdapter();

        $db->query("UPDATE `{$this->getTable('mana_seo/url')}` SET ".
            "`final_url_key` = COALESCE(`manual_url_key`, `url_key`), ".
            "`final_include_filter_name` = COALESCE(`force_include_filter_name`, `include_filter_name`)");
    }

    public function processConflicts($options) {
    }

}