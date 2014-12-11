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
        if (Mage::app()->getRequest()->getParam($model->getRequestVar()) == $model->getResetValue() &&
            !($this->coreHelper()->isSpecialPagesInstalled() && $this->specialPageHelper()->isAppliedInFilter($model->getRequestVar())))
        {
            return false;
        }
        return $model->getRemoveUrl();
    }

    #region Dependencies

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    /**
     * @return Mana_Page_Helper_Special
     */
    public function specialPageHelper() {
        return Mage::helper('mana_page/special');
    }
    #endregion
}