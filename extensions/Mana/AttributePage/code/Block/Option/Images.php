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
abstract class Mana_AttributePage_Block_Option_Images extends Mage_Core_Block_Template {
    /**
     * @return Mana_AttributePage_Resource_OptionPage_Store_Collection
     */
    abstract public function getCollection();

    #region Dependencies
    /**
     * @return Mana_Core_Helper_Files|Mage_Core_Helper_Abstract
     */
    public function filesHelper() {
        return Mage::helper('mana_core/files');
    }
    #endregion
}