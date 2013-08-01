<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterAjax
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/* BASED ON SNIPPET: New Module/Helper/Data.php */
/**
 * Generic helper functions for ManaPro_FilterAjax module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class ManaPro_FilterAjax_Helper_Data extends Mage_Core_Helper_Abstract {
    /**
     * @return ManaPro_FilterAjax_Helper_PageType[]
     */
    public function getPageTypes() {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        return $core->getPageTypes(/*'filter_ajax_helper'*/);
    }

    /**
     * @param string $type
     * @return ManaPro_FilterAjax_Helper_PageType
     */
    public function getPageType($type) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');

        return $core->getPageType($type/*, 'filter_ajax_helper'*/);
    }
}