<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterClear
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * Generic helper functions for ManaPro_FilterClear module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class ManaPro_FilterClear_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getClearUrl($filterBlock) {
        if (!Mage::getStoreConfigFlag('mana_filters/advanced/clear')) {
            return false;
        }
        if (!($model = $filterBlock->getFilter())) {
            return false;
        }
        if (Mage::app()->getRequest()->getParam($model->getRequestVar()) == $model->getResetValue()) {
            return false;
        }
        return $model->getRemoveUrl();
    }
}