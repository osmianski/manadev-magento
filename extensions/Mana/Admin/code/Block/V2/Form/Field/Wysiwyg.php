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
class Mana_Admin_Block_V2_Form_Field_Wysiwyg extends Varien_Data_Form_Element_Textarea {
    /**
     * Retrieve additional html and put it at the end of element html
     *
     * @return string
     */
    public function getAfterElementHtml()
    {
        $this->jsHelper()->setConfig('url.wysiwyg', $this->urlTemplateHelper()->encodeAttribute(
            $this->createUrl()->getUrl('adminhtml/mana/wysiwyg', array(
                'element_id' => '__0__',
                'store_id' => $this->adminHelper()->getStore()->getId(),
            ))
        ));
        $html = parent::getAfterElementHtml();
        if ($this->getIsWysiwygEnabled()) {
            $disabled = ($this->getDisabled() || $this->getReadonly());
            $html .= Mage::getSingleton('core/layout')
                ->createBlock('adminhtml/widget_button', '', array(
                    'label'   => Mage::helper('catalog')->__('WYSIWYG Editor'),
                    'type'    => 'button',
                    'disabled' => $disabled,
                    'class' => ($disabled) ? 'disabled' : '',
                ))->toHtml();
        }
        return $html;
    }

    /**
     * Check whether wysiwyg enabled or not
     *
     * @return boolean
     */
    public function getIsWysiwygEnabled()
    {
        return Mage::getSingleton('cms/wysiwyg_config')->isEnabled();
    }

    #region Dependencies
    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    /**
     * @return Mana_Core_Helper_Js
     */
    public function jsHelper() {
        return Mage::helper('mana_core/js');
    }

    /**
     * @return Mana_Core_Helper_UrlTemplate
     */
    public function urlTemplateHelper() {
        return Mage::helper('mana_core/urlTemplate');
    }

    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }

    /**
     * @return Mage_Adminhtml_Model_Url
     */
    public function createUrl() {
        return Mage::getModel('adminhtml/url');
    }

    #endregion
}