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
class ManaPro_FilterContent_Model_Condition_PageType extends Mana_Core_Model_Condition_Abstract {
    protected function _construct() {
        parent::_construct();
        $this->_data['attribute'] = '_page_type';
    }

    #region Attribute
    /**
     * @return Mage_Eav_Model_Entity_Attribute_Abstract|mixed|Varien_Object
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setData('show_as_text', true);
        return $element;
    }
    public function getAttributeSelectOptions() {
        return array();
    }
    public function getAttributeName() {
        return $this->contentHelper()->__('Page Type');
    }
    #endregion
    #region Operator
    public function loadOperatorOptions()
    {
        $this->setData('operator_option', array(
            '()'  => $this->contentHelper()->__('one of these'),
            '!()'  => $this->contentHelper()->__('not any of these'),
        ));
        return $this;
    }
    #endregion
    #region Value
    public function getValueElementType() {
        return 'multiselect';
    }

    public function loadValueOptions()
    {
        $selectOptions = array('' => '');
        foreach ($this->coreHelper()->getPageTypes() as $pageType) {
            if ($label = $pageType->getConditionLabel()) {
                $selectOptions[$pageType->getCode()] = $label;
            }
        }
        $this->setData('value_option', $selectOptions);
        return $this;
    }
    #endregion
    #region Dependencies

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    /**
     * @return Mana_Core_Helper_Data
     */
    public function contentHelper() {
        return Mage::helper('manapro_filtercontent');
    }
    #endregion
}