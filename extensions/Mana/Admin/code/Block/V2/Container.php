<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method bool getIsTabContainer()
 * @method Mana_Admin_Block_V2_Container setIsTabContainer(bool $value)
 */
class Mana_Admin_Block_V2_Container extends Mage_Adminhtml_Block_Widget_Container {
    public function __construct() {
        parent::__construct();
        $this->setTemplate('mana/admin/v2/container.phtml');
    }

    #region Dependencies
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
    #endregion
}