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
class Mana_Page_Block_Adminhtml_Special_Form extends Mana_Admin_Block_V3_Form
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'mf_special',
            'html_id_prefix' => 'mf_special_',
            'field_container_id_prefix' => 'mf_special_tr_',
            'use_container' => true,
            'method' => 'post',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'field_name_suffix' => 'fields',
            'flat_model' => $this->getFlatModel(),
            'edit_model' => $this->getEditModel(),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_general', array(
            'title' => $this->__('General'),
            'legend' => $this->__('General'),
        ));

        $this->addField($fieldset, 'title', 'text', array(
            'label' => $this->__('Title'),
            'title' => $this->__('Title'),
            'name' => 'title',
            'required' => true,

            'default_bit_no' => Mana_Page_Model_Special::DM_TITLE,
            'default_store_label' => $this->__('Same for All Stores'),
        ));

        $this->addField($fieldset, 'url_key', 'text', array(
            'label' => $this->__('URL Key'),
            'title' => $this->__('URL Key'),
            'name' => 'url_key',
            'required' => true,

            'default_bit_no' => Mana_Page_Model_Special::DM_URL_KEY,
            'default_store_label' => $this->__('Same for All Stores'),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_condition', array(
            'title' => $this->__('Condition'),
            'legend' => $this->__('Condition'),
        ));

        $this->addField($fieldset, 'condition', 'textarea', array(
            'label' => $this->__('Condition'),
            'title' => $this->__('Condition'),
            'name' => 'condition',
            'required' => true,
            'disabled' => !$this->adminHelper()->isGlobal(),
        ));

        if ($this->coreHelper()->isManadevLayeredNavigationInstalled()) {
            $fieldset = $this->addFieldset($form, 'mfs_layered_nav', array(
                'title' => $this->__('Layered Navigation'),
                'legend' => $this->__('Layered Navigation'),
            ));

            $this->addField($fieldset, 'filter', 'select', array(
                'label' => $this->__('Add as an Option to Filter'),
                'title' => $this->__('Add as an Option to Filter'),
                'options' => Mage::getModel('mana_filters/source_filter')->exclude('category')->getOptionArray(),
                'name' => 'filter',
                'required' => false,
                'disabled' => !$this->adminHelper()->isGlobal(),
            ));

        }

        $this->setForm($form);
        return parent::_prepareForm();
    }

    #region Dependencies
    /**
     * @return Mana_Page_Model_Special
     */
    public function getFlatModel() {
        return Mage::registry('m_flat_model');
    }

    /**
     * @return Mana_Page_Model_Special
     */
    public function getEditModel() {
        return Mage::registry('m_edit_model');
    }


    #endregion
}