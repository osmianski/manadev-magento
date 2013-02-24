<?php
/**
 * @category    Mana
 * @package     ManaPro_Guestbook
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Guestbook_Model_Post extends Mana_Db_Model_Object {
    /**
     * Invoked during model creation process, this method associates this model with resource and resource
     * collection classes
     */
    protected function _construct() {
        $this->_init('manapro_guestbook/post');
    }
    protected function _validate($result) {
        if (trim($this->getCreatedAt()) === '') {
            $result->addError(Mage::helper('manapro_guestbook')->__('Please fill in %s field',
                Mage::helper('manapro_guestbook')->__('Date')));
        }
        $filterInput = new Zend_Filter_LocalizedToNormalized(array(
            'date_format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        ));
        $filterInternal = new Zend_Filter_NormalizedToLocalized(array(
            'date_format' => Varien_Date::DATE_INTERNAL_FORMAT
        ));
        $this->setCreatedAt($filterInternal->filter($filterInput->filter($this->getCreatedAt())));

        foreach (Mage::helper('manapro_guestbook')->getRequiredFields() as $field) {
            $method = "_validateField_$field";
            $this->$method($result);
        }
    }
    protected function _validateField_email($result) {
        if (trim($this->getEmail()) === '') {
            $result->addError(Mage::helper('manapro_guestbook')->__('Please fill in %s field',
                Mage::helper('manapro_guestbook')->__('Email')));
        }
    }
    protected function _validateField_url($result) {
        if (trim($this->getUrl()) === '') {
            $result->addError(Mage::helper('manapro_guestbook')->__('Please fill in %s field',
                Mage::helper('manapro_guestbook')->__('Website')));
        }
    }
    protected function _validateField_name($result) {
        if (trim($this->getName()) === '') {
            $result->addError(Mage::helper('manapro_guestbook')->__('Please fill in %s field',
                Mage::helper('manapro_guestbook')->__('Name')));
        }
    }
    protected function _validateField_text($result) {
        if (trim($this->getText()) === '') {
            $result->addError(Mage::helper('manapro_guestbook')->__('Please fill in %s field',
                Mage::helper('manapro_guestbook')->__('Text')));
        }
    }
    protected function _validateField_country($result) {
    }
    protected function _validateField_region($result) {
        if (Mage::getStoreConfigFlag('manapro_guestbook/region/is_freeform')) {
            if (trim($this->getRegion()) === '') {
                $result->addError(Mage::helper('manapro_guestbook')->__('Please fill in %s field',
                    Mage::helper('manapro_guestbook')->__('Region')));
            }
        }
    }
}