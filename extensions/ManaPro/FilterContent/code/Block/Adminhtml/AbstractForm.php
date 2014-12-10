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
class ManaPro_FilterContent_Block_Adminhtml_AbstractForm extends Mana_Admin_Block_V3_Form {
    #region Dependencies
    /**
     * @return ManaPro_FilterContent_Model_Abstract
     */
    public function getFlatModel() {
        return Mage::registry('m_flat_model');
    }

    /**
     * @return ManaPro_FilterContent_Model_Abstract
     */
    public function getEditModel() {
        return Mage::registry('m_edit_model');
    }

    #endregion
}