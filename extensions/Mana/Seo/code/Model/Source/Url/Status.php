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
class Mana_Seo_Model_Source_Url_Status extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        return array(
            array('value' => Mana_Seo_Model_Url::STATUS_ACTIVE, 'label' => $this->helper()->__('Active')),
            array('value' => Mana_Seo_Model_Url::STATUS_OBSOLETE, 'label' => $this->helper()->__('Redirect')),
            array('value' => Mana_Seo_Model_Url::STATUS_DISABLED, 'label' => $this->helper()->__('Disabled')),
        );
    }

    #region Dependencies
    /**
     * @return Mana_Seo_Helper_Data
     */
    public function helper() {
        return Mage::helper('mana_seo');
    }
    #endregion
}