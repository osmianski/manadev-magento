<?php
/** 
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_AttributePage_Block_Adminhtml_AttributePage_AttributeForm extends Mana_AttributePage_Block_Adminhtml_AttributePage_AbstractForm  {
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'mf_attribute',
            'html_id_prefix' => 'mf_attribute_',
            'field_container_id_prefix' => 'mf_attribute_tr_',
            'use_container' => true,
            'method' => 'post',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'field_name_suffix' => 'fields',
            'flat_model' => $this->getFlatModel(),
            'edit_model' => $this->getEditModel(),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_attribute', array(
            'title' => $this->__('Based On Attribute(s)'),
            'legend' => $this->__('Based On Attribute(s)'),
        ));

        $this->addField($fieldset, 'attribute_id_0', 'select', array(
            'label' => $this->__('Attribute'),
            'title' => $this->__('Attribute'),
            'name' => 'attribute_id_0',
            'required' => true,
            'options' => $this->getAttributeSourceModel()->getOptionArray(),
        ));

        $this->addField($fieldset, 'attribute_id_1', 'select', array(
            'label' => $this->__('Attribute'),
            'title' => $this->__('Attribute'),
            'name' => 'attribute_id_1',
            'required' => false,
            'options' => $this->getAttributeSourceModel()->getOptionArray(),
        ));

        $this->addField($fieldset, 'attribute_id_2', 'select', array(
            'label' => $this->__('Attribute'),
            'title' => $this->__('Attribute'),
            'name' => 'attribute_id_2',
            'required' => false,
            'options' => $this->getAttributeSourceModel()->getOptionArray(),
        ));

        $this->addField($fieldset, 'attribute_id_3', 'select', array(
            'label' => $this->__('Attribute'),
            'title' => $this->__('Attribute'),
            'name' => 'attribute_id_3',
            'required' => false,
            'options' => $this->getAttributeSourceModel()->getOptionArray(),
        ));

        $this->addField($fieldset, 'attribute_id_4', 'select', array(
            'label' => $this->__('Attribute'),
            'title' => $this->__('Attribute'),
            'name' => 'attribute_id_4',
            'required' => false,
            'options' => $this->getAttributeSourceModel()->getOptionArray(),
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }
}