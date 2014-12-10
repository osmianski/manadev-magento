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
abstract class Mana_AttributePage_Resource_OptionPage_Abstract extends Mage_Core_Model_Mysql4_Abstract {
    #region Dependencies
    /**
     * @return Mana_AttributePage_Helper_Data
     */
    public function attributePageHelper() {
        return Mage::helper('mana_attributepage');
    }
    #endregion
}