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
class ManaPro_FilterContent_Model_Condition_Filter extends Mana_Core_Model_Condition_Abstract {
    #region Attribute
    public function getAttributeElementHtml() {
        return parent::getAttributeElementHtml() . $this->contentHelper()->__('filter');
    }

    /**
     * @return Mage_Eav_Model_Entity_Attribute_Abstract|mixed|Varien_Object
     */
    public function getAttributeObject() {
        if (!($obj = $this->_getData('attribute_object'))) {
            try {
                $obj = Mage::getSingleton('eav/config')
                    ->getAttribute('catalog_product', $this->getData('attribute'));
            } catch (Exception $e) {
                $obj = new Varien_Object();
                $obj
                    ->setData('entity', Mage::getResourceSingleton('catalog/product'))
                    ->setData('frontend_input', 'text');
            }
            foreach ($this->getFilterResource()->getAttributes($this->getData('attribute')) as $filter) {
                $obj->setData('frontend_label', $filter['label']);
                break;
            }
            $this->setData('attribute_object', $obj);
        }

        return $obj;
    }

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
        return $this->getAttributeObject()->getData('frontend_label');
    }
    #endregion
    #region Dependencies

    /**
     * @return ManaPro_FilterContent_Resource_Filter
     */
    public function getFilterResource() {
        return Mage::getResourceSingleton('manapro_filtercontent/filter');
    }

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