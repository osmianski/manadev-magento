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
class ManaPage_Sale_Helper_Special_SpecialPrice extends Mana_Page_Helper_Special_Rule {
    public function join($select, $xml) {
        $this->eavHelper()->joinAttribute($select, 'special_from_date');
        $this->eavHelper()->joinAttribute($select, 'special_to_date');
    }

    public function where($xml) {
        $from = $this->eavHelper()->attributeValue('special_from_date');
        $to = $this->eavHelper()->attributeValue('special_to_date');
        $today = "'" . Mage::app()->getLocale()->date()->toString(Varien_Date::DATE_INTERNAL_FORMAT) . "'";

        return "$from <= $today AND ($today <= $to OR $to IS NULL)";
    }
}