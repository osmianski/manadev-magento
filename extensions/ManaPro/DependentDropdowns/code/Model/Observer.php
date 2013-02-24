<?php
/**
 * @category    Mana
 * @package     ManaPro_DependentDropdowns
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods -
 * handlers for these events.
 * @author Mana Team
 *
 */
class ManaPro_DependentDropdowns_Model_Observer {
    /**
     * REPLACE THIS WITH DESCRIPTION (handles event "adminhtml_catalog_product_attribute_edit_prepare_form")
     * @param Varien_Event_Observer $observer
     */
    public function extendAttributeForm($observer) {
        /* @var $form Varien_Data_Form */ $form = $observer->getEvent()->getForm();
        /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */ $attribute = $observer->getEvent()->getAttribute();
        $helper = Mage::helper('manapro_dependentdropdowns');
        $fieldset = $form->addFieldset('m_dependent_fieldset', array(
            'title' => $helper->__('Dropdown Dependency'),
            'legend' => $helper->__('Dropdown Dependency'),
        ), 'base_fieldset');

        $fieldset->addField('m_dependent_on', 'select', array(
            'label' => $helper->__('Depends On'),
            'note' => $helper->__('If set, this attribute will be disabled in product editing page and in dependent dropdown navigation block in frontend until user selects a value for attribute specified in this field.'),
            'name' => 'm_dependent_on',
            'required' => false,
            'options' => Mage::getSingleton('manapro_dependentdropdowns/source_attribute')->getOptionArray(),
        ));
    }
}