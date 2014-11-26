<?php
/** 
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Content_Block_Filter_Search extends Mage_Core_Block_Template {

    public function __construct() {
        $this->setTemplate('manapro/content/filter/search.phtml');
    }

    #region Dependencies
    /**
     * @return Mana_Content_Helper_Data
     */
    public function contentHelper() {
        return Mage::helper('mana_content');
    }
    #endregion
}