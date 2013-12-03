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
class Mana_AttributePage_Block_Adminhtml_AttributePage_DesignForm extends Mana_Admin_Block_V2_Form
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'mf_design',
            'html_id_prefix' => 'mf_design_',
            'field_container_id_prefix' => 'mf_design_tr_',
            'use_container' => true,
            'method' => 'post',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'field_name_suffix' => 'fields',
            'flat_model' => $this->getFlatModel(),
            'edit_model' => $this->getEditModel(),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_design', array(
            'title' => $this->__('Design'),
            'legend' => $this->__('Design'),
        ));

        $this->addField($fieldset, 'sample', 'label', array(
            'label' => $this->__('Sample'),
            'name' => 'sample',
            'bold' => true,
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

    #endregion
}