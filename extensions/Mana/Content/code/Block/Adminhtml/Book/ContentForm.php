<?php
/** 
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Content_Block_Adminhtml_Book_ContentForm extends Mana_Content_Block_Adminhtml_Book_AbstractForm
{
    protected function _prepareLayout() {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $headBlock = $this->getLayout()->getBlock('head');
            if($headBlock) {
                $headBlock->setData('can_load_tiny_mce', true);
            }
        }
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'mf_content',
            'html_id_prefix' => 'mf_content_',
            'field_container_id_prefix' => 'mf_content_tr_',
            'use_container' => true,
            'method' => 'post',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'field_name_suffix' => 'fields',
            'flat_model' => $this->getFlatModel(),
            'edit_model' => $this->getEditModel(),
        ));


        $fieldset = $this->addFieldset($form, 'mfs_title', array(
            'title' => $this->__('Title'),
            'legend' => $this->__('Title')
        ));

        $this->addField($fieldset, 'title', 'text', array(
            'label' => $this->__('Title'),
            'title' => $this->__('Title'),
            'name' => 'title',
            'required' => true,

            'default_bit_no' => Mana_Content_Model_Page_Abstract::DM_TITLE,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_content', array(
            'title' => $this->__('Content'),
            'class' => 'fieldset-wide',
            'legend' => $this->__('Content'),
        ));

        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
            array(
                'tab_id' => $this->getTabId()
            )
        );

        $contentFieldType = is_null($this->getFlatModel()->getData('reference_id')) ? 'editor': 'textarea';

        $contentField = $fieldset->addField('content', $contentFieldType, array(
            'name'      => 'content',
            'style'     => 'height:36em;',
            'required'  => true,
            'disabled'  => false,
            'config'    => $wysiwygConfig,

            'default_bit_no' => Mana_Content_Model_Page_Abstract::DM_CONTENT,
            'default_store_label' => $this->__('Same For All Stores'),
        ));

        $this->addField($fieldset, 'reference_id', 'hidden', array(
            'name'      => 'reference_id',
        ));

        // Setting custom renderer for content field to remove label column

        $renderer = $this->getLayout()->createBlock('mana_content/wysiwyg');
        $contentField->setRenderer($renderer);

        $this->setForm($form);
        return parent::_prepareForm();
    }
}