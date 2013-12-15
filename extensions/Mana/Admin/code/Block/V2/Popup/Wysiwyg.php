<?php
/** 
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Admin_Block_V2_Popup_Wysiwyg extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form.
     * Adding editor field to render
     *
     * @return Mage_Adminhtml_Block_Catalog_Helper_Form_Wysiwyg_Content
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'wysiwyg_edit_form', 'action' => $this->getData('action'), 'method' => 'post'));

        $config['document_base_url']     = $this->getData('store_media_url');
        $config['store_id']              = $this->getData('store_id');
        $config['add_variables']         = false;
        $config['add_widgets']           = false;
        $config['add_directives']        = true;
        $config['use_container']         = true;
        $config['container_class']       = 'hor-scroll';

        $form->addField($this->getData('editor_element_id'), 'editor', array(
            'name'      => 'content',
            'style'     => 'width:600px;height:520px',
            'required'  => true,
            'force_load' => true,
            'config'    => Mage::getSingleton('cms/wysiwyg_config')->getConfig($config),
        ));
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
