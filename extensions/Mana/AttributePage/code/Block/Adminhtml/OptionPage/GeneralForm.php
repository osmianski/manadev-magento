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
class Mana_AttributePage_Block_Adminhtml_OptionPage_GeneralForm extends Mana_AttributePage_Block_Adminhtml_OptionPage_AbstractForm
{
    protected function _prepareLayout() {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setData('can_load_tiny_mce', true);
        }
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'mf_general',
            'html_id_prefix' => 'mf_general_',
            'field_container_id_prefix' => 'mf_general_tr_',
            'use_container' => true,
            'method' => 'post',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'field_name_suffix' => 'fields',
            'flat_model' => $this->getFlatModel(),
            'edit_model' => $this->getEditModel(),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_option', array(
            'title' => $this->__('Based On Option(s)'),
            'legend' => $this->__('Based On Option(s)'),
        ));

        $attributePage = $this->adminHelper()->isGlobal()
            ? $this->getAttributePage()
            : $this->getGlobalAttributePage();
        $attributeLabels = $this->getAttributeSourceModel()->getAllOptions();
        for ($i = 0; $i < Mana_AttributePage_Model_AttributePage_Abstract::MAX_ATTRIBUTE_COUNT; $i++) {
            if ($attributeId = $attributePage->getData("attribute_id_$i")) {
                $label = $attributeId;
                if (($index = $this->coreHelper()->arrayFind($attributeLabels, 'value', $attributeId)) !== false) {
                    $label = $this->escapeHtml($attributeLabels[$index]['label']);
                }
                $this->addField($fieldset, "option_id_$i", 'select_text', array(
                    'label' => $label,
                    'title' => $label,
                    'name' => "option_id_$i",
                    'bold' => true,
                    'options' => $this->getOptionSourceModel($attributeId)->getOptionArray(),
                ));
            }
        }

        $fieldset = $this->addFieldset($form, 'mfs_content', array(
            'title' => $this->__('Content'),
            'legend' => $this->__('Content'),
        ));

        $this->addField($fieldset, 'title', 'text', array(
            'label' => $this->__('Title'),
            'title' => $this->__('Title'),
            'name' => 'title',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_TITLE,
            'default_label' => $this->__('Use Option Labels'),
            'default_store_label' => $this->__('Use Option Labels'),
        ));

        $this->addField($fieldset, 'description', 'wysiwyg', array(
            'name'      => 'description',
            'label'     => $this->__('Description'),
            'title'     => $this->__('Description'),
            'required'  => true,
            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_DESCRIPTION,
            'default_label' => $this->__('Use Title'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'description_bottom', 'wysiwyg', array(
            'label' => $this->__('Bottom Description'),
            'title' => $this->__('Bottom '),
            'name' => 'description_bottom',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_DESCRIPTION_BOTTOM,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_base_image', array(
            'title' => $this->__('Base Image'),
            'legend' => $this->__('Base Image'),
        ));

        $this->addField($fieldset, 'image', 'image', array(
            'label' => $this->__('Image'),
            'title' => $this->__('Image'),
            'note' => $this->__('Visible on the top of option page'),
            'name' => 'image',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE,
            'default_label' => $this->__('Same For All Option Pages'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'image_width', 'text', array(
            'label' => $this->__('Width'),
            'title' => $this->__('Width'),
            'name' => 'image_width',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE_WIDTH,
            'default_label' => $this->__('Same For All Option Pages'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'image_height', 'text', array(
            'label' => $this->__('Height'),
            'title' => $this->__('Height'),
            'name' => 'image_height',
            'required' => false,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_IMAGE_HEIGHT,
            'default_label' => $this->__('Same For All Option Pages'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_other', array(
            'title' => $this->__('Other Settings'),
            'legend' => $this->__('Other Settings'),
        ));

        $this->addField($fieldset, 'is_active', 'select', array(
            'label' => $this->__('Status'),
            'title' => $this->__('Status'),
            'options' => $this->getStatusSourceModel()->getOptionArray(),
            'name' => 'is_active',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_IS_ACTIVE,
            'default_label' => $this->__('Same For All Option Pages'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'include_in_menu', 'select', array(
            'label' => $this->__('Include In Top Menu'),
            'title' => $this->__('Include In Top Menu'),
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
            'name' => 'include_in_menu',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_INCLUDE_IN_MENU,
            'default_label' => $this->__('Same For All Option Pages'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'is_featured', 'select', array(
            'label' => $this->__('Featured'),
            'title' => $this->__('Featured'),
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
            'name' => 'is_featured',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_IS_FEATURED,
            'default_label' => $this->__('Same For All Option Pages'),
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'position', 'text', array(
            'label' => $this->__('Position'),
            'title' => $this->__('Position'),
            'name' => 'position',
            'required' => true,

            'default_bit_no' => Mana_AttributePage_Model_OptionPage_Abstract::DM_POSITION,
            'default_label' => $this->__('Use Option Positions'),
            'default_store_label' => $this->__('Use Option Positions'),
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }
}