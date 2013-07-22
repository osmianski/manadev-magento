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
class Mana_Admin_Controller_V2_Controller extends Mage_Adminhtml_Controller_Action {
    protected function _processPendingRawEdits(&$raw, &$edit) {
        foreach ($edit['pending'] as $id => $cells) {
            if (isset($edit['deleted'][$id])) {
                continue;
            }

            foreach ($cells as $column => $compositeValue) {
                $raw[$id][$column] = $compositeValue['value'];
            }
            $edit['saved'][$id] = $id;
        }
        $edit['pending'] = array();

        return $this;
    }

    #region Dependencies
    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }

    /**
     * @return Mana_Db_Helper_Data
     */
    public function dbHelper() {
        return Mage::helper('mana_db');
    }

    /**
     * @return Mage_Adminhtml_Model_Session
     */
    public function getSessionSingleton() {
        return Mage::getSingleton('adminhtml/session');
    }
    #endregion
}