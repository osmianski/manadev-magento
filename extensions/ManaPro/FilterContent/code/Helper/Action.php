<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterContent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
abstract class ManaPro_FilterContent_Helper_Action extends Mage_Core_Helper_Abstract {
    protected function _normalize($action) {
        return array_merge(array(
            'meta_title' => '{{ meta_title }}',
        ), $action);
    }
}