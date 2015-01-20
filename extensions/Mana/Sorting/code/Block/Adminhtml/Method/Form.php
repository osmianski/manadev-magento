<?php
/** 
 * @category    Mana
 * @package     Mana_Sorting
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Sorting_Block_Adminhtml_Method_Form extends Mana_Sorting_Block_Adminhtml_Method_AbstractForm {

    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'mf_method',
            'html_id_prefix' => 'mf_method_',
            'field_container_id_prefix' => 'mf_method_tr_',
            'use_container' => true,
            'method' => 'post',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'field_name_suffix' => 'fields',
            'flat_model' => $this->getFlatModel(),
            'edit_model' => $this->getEditModel(),
        ));


        $fieldset = $this->addFieldset($form, 'mfs_general', array(
            'title' => $this->__('General'),
            'legend' => $this->__('General')
        ));

        $this->addField($fieldset, 'title', 'text', array(
            'label' => $this->__('Title'),
            'title' => $this->__('Title'),
            'name' => 'title',
            'required' => true,

            'default_bit_no' => Mana_Sorting_Model_Method_Abstract::DM_TITLE,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'url_key', 'text', array(
            'label' => $this->__('URL Key'),
            'title' => $this->__('URL Key'),
            'name' => 'url_key',
            'required' => true,
            'default_bit_no' => Mana_Sorting_Model_Method_Abstract::DM_URL_KEY,
            'default_label' => $this->__('Use Title'),
            'default_store_label' => $this->__(
                !$this->adminHelper()->isGlobal() && $this->coreDbHelper()
                    ->isModelContainsCustomSetting($this->getGlobalEditModel(), Mana_Sorting_Model_Method_Abstract::DM_URL_KEY)
                    ? 'Same For All Stores' : 'Use Title'),
        ));

        $this->addField($fieldset, 'position', 'text', array(
            'label' => $this->__('Position'),
            'title' => $this->__('Position'),
            'name' => 'position',

            'default_bit_no' => Mana_Sorting_Model_Method_Abstract::DM_POSITION,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'is_active', 'select', array(
            'label' => $this->__('Status'),
            'title' => $this->__('Status'),
            'options' => $this->getStatusSourceModel()->getOptionArray(),
            'name' => 'is_active',
            'required' => true,
            'default_bit_no' => Mana_Sorting_Model_Method_Abstract::DM_IS_ACTIVE,
            'default_store_label' => $this->__('Same For All Stores'),
        ));


        $fieldset = $this->addFieldset($form, 'mfs_attribute', array(
            'title' => $this->__('Combination of Sorting Methods'),
            'legend' => $this->__('Combination of Sorting Methods'),
        ));

        for($x=0;$x<=4;$x++) {
            $this->addField($fieldset, 'attribute_id_'.$x, 'select', array(
                'label' => ($x == 0) ? $this->__('Sort By') : $this->__('Then By'),
                'title' => ($x == 0) ? $this->__('Sort By') : $this->__('Then By'),
                'name' => 'attribute_id_'.$x,
                'required' => $x == 0 || $x == 1,
                'options' => $this->getAttributeSourceModel()->getOptionArray(),
                'disabled' => !(bool)$this->adminHelper()->isGlobal(),
            ));
            $this->addField($fieldset, 'attribute_id_'.$x.'_sortdir', 'select', array(
                'label' => $this->__('Direction'),
                'title' => $this->__('Direction'),
                'name' => 'attribute_id_'.$x.'_sortdir',
                'required' => $x == 0 || $x == 1,
                'options' => $this->getDirectionSourceModel()->getOptionArray(),
                'disabled' => !(bool)$this->adminHelper()->isGlobal(),
            ));
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
}