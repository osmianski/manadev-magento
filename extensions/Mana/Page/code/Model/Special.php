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
    const DM_POSITION = 3;
    const ENTITY = 'mana_page/special';

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

        if ($this->dbHelper()->isModelContainsCustomSetting($this, self::DM_POSITION) &&
            (!trim($this->getData('position')) || !is_numeric($this->getData('position'))))
        {
            $errors[] = $t->__('Please fill in numeric %s field', $t->__('Position'));
        }

        if (!trim($this->getData('condition'))) {
            $errors[] = $t->__('Please fill in %s field', $t->__('Condition'));
        }
        else {
            $xml = null;
            try {
                $xml = new SimpleXMLElement($this->getData('condition'));
            }
            catch (Exception $e) {
                //$errors[] = $t->__('%s field is not valid XML', $t->__('Condition'));
            }
            if (is_null($xml)) {
                $errors[] = $t->__('%s field is not valid XML', $t->__('Condition'));
            }
            else {
                $this->_validateXmlRecursively($xml, $errors);
            }
        }

        if (count($errors)) {
			throw new Mana_Core_Exception_Validation($errors);
        }
    }

    /**
     * @param SimpleXmlElement $xml
     * @param string[] $errors
     */
    protected function _validateXmlRecursively($xml, &$errors) {
        /* @var $t Mana_Page_Helper_Data */
        $t = Mage::helper('mana_page');

        $helper = (string)Mage::getConfig()->getNode('mana_page/special/' . $xml->getName());
        if (!$helper) {
            $errors[] = $t->__('Unknown %s rule in %s field', $xml->getName(), $t->__('Condition'));
        }

        foreach ($xml->children() as $childXml) {
            $this->_validateXmlRecursively($childXml, $errors);
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