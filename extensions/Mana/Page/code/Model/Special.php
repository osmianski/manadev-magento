<?php
/** 
 * @category    Mana
 * @package     Mana_Page
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Page_Model_Special extends Varien_Object {
    const DM_TITLE = 1;
    const DM_URL_KEY = 2;

    public function validate() {
        /* @var $t Mana_Page_Helper_Data */
        $t = Mage::helper('mana_page');
        $errors = array();

        if ($this->dbHelper()->isModelContainsCustomSetting($this, self::DM_TITLE) &&
            !trim($this->getData('title')))
        {
            $errors[] = $t->__('Please fill in %s field', $t->__('Title'));
        }

        if ($this->dbHelper()->isModelContainsCustomSetting($this, self::DM_URL_KEY) &&
            !trim($this->getData('url_key')))
        {
            $errors[] = $t->__('Please fill in %s field', $t->__('URL Key'));
        }

        if (!trim($this->getData('condition'))) {
            $errors[] = $t->__('Please fill in %s field', $t->__('Condition'));
        }

        if (count($errors)) {
			throw new Mana_Core_Exception_Validation($errors);
        }
    }

   #region Dependencies

    /**
     * @return Mana_Core_Helper_Db
     */
    public function dbHelper() {
        return Mage::helper('mana_core/db');
    }

    #endregion
}