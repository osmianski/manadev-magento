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
class ManaPro_FilterContent_Block_LayeredDescription extends Mage_Core_Block_Template {
    public function getBackgroundImage() {
        return $this->_getData('background_image');
    }

    public function getAdditionalDescription() {
        return $this->_getData('additional_description');
    }

    protected function _construct() {
        $this->setTemplate('manapro/filtercontent/layered-description.phtml');
    }

    #region Dependencies
    /**
     * @return Mana_Core_Helper_Files
     */
    public function fileHelper() {
        return Mage::helper('mana_core/files');
    }

    #endregion
}