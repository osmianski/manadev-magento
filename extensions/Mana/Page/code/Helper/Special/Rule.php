<?php
/** 
 * @category    Mana
 * @package     Mana_Page
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
abstract class Mana_Page_Helper_Special_Rule extends Mage_Core_Helper_Abstract {
    abstract public function join($select, $xml);
    abstract public function where($xml);

    #region Dependencies

    /**
     * @return Mana_Page_Helper_Special
     */
    public function specialPageHelper() {
        return Mage::helper('mana_page/special');
    }

    /**
     * @return Mana_Core_Helper_Eav
     */
    public function eavHelper() {
        return Mage::helper('mana_core/eav');
    }
    #endregion
}