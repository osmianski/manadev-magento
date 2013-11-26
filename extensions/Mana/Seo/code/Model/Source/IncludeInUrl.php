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
class Mana_Seo_Model_Source_IncludeInUrl extends Mana_Core_Model_Source_Abstract {
    const ALWAYS = 'always';
    const NEVER  = 'never';
    const AS_IN_SCHEMA = 'as_in_schema';

    protected function _getAllOptions() {
        return array(
            array('value' => self::AS_IN_SCHEMA, 'label' => Mage::helper('mana_seo')->__('As specified in current SEO schema settings')),
            array('value' => self::ALWAYS, 'label' => Mage::helper('mana_seo')->__('Always')),
            array('value' => self::NEVER, 'label' => Mage::helper('mana_seo')->__('Never')),
        );
    }
}