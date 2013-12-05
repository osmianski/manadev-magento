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
class Mana_AttributePage_Block_Adminhtml_AttributePage_AttributeForm extends Mana_Admin_Block_V2_Form  {
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
            'options' => $this->getSourceModel()->getOptionArray(),
        ));

        $this->addField($fieldset, 'attribute_id_1', 'select', array(
            'label' => $this->__('Attribute'),
            'title' => $this->__('Attribute'),
            'name' => 'attribute_id_1',
            'required' => false,
            'options' => $this->getSourceModel()->getOptionArray(),
        ));

        $this->addField($fieldset, 'attribute_id_2', 'select', array(
            'label' => $this->__('Attribute'),
            'title' => $this->__('Attribute'),
            'name' => 'attribute_id_2',
            'required' => false,
            'options' => $this->getSourceModel()->getOptionArray(),
        ));

        $this->addField($fieldset, 'attribute_id_3', 'select', array(
            'label' => $this->__('Attribute'),
            'title' => $this->__('Attribute'),
            'name' => 'attribute_id_3',
            'required' => false,
            'options' => $this->getSourceModel()->getOptionArray(),
        ));

        $this->addField($fieldset, 'attribute_id_4', 'select', array(
            'label' => $this->__('Attribute'),
            'title' => $this->__('Attribute'),
            'name' => 'attribute_id_4',
            'required' => false,
            'options' => $this->getSourceModel()->getOptionArray(),
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }

    #region Dependencies
    /**
     * @return Mana_AttributePage_Model_AttributePage_Abstract
     */
    public function getFlatModel() {
        return Mage::registry('m_flat_model');
    }

    /**
     * @return Mana_AttributePage_Model_AttributePage_Abstract
     */
    public function getEditModel() {
        return Mage::registry('m_edit_model');
    }

    /**
     * @return Mana_AttributePage_Model_Source_Attribute
     */
    public function getSourceModel() {
        return Mage::getSingleton('mana_attributepage/source_attribute');
    }
    #endregion
}