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
class Mana_Seo_Model_Source_Schema_ExplainedStatus extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        return array(
            array('value' => Mana_Seo_Model_Schema::STATUS_ACTIVE, 'label' => $this->helper()->__('Active - used for link generation and processing')),
            array('value' => Mana_Seo_Model_Schema::STATUS_OBSOLETE, 'label' => $this->helper()->__('Redirect - links are redirected to active schema')),
            array('value' => Mana_Seo_Model_Schema::STATUS_DISABLED, 'label' => $this->helper()->__('Disabled - not used')),
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