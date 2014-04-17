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
class Mana_Admin_Block_V2_Form_Field_Image extends Varien_Data_Form_Element_Text {
    public function getElementHtml() {
        $this->jsHelper()->setConfig('url.upload', Mage::getModel('adminhtml/url')->getUrl('*/upload/start'));
        $this->jsHelper()->setConfig('url.imageBase', $this->fileHelper()->getBaseUrl('image'));
        $src = $this->getValue();
        $src = $src ? $this->fileHelper()->getUrl($src, array('temp/image', 'image')) : '';
        $html = <<<EOT
            <input type="hidden" id="{$this->getHtmlId()}" name="{$this->getName()}"
                value="{$this->getEscapedValue()}" {$this->serialize($this->getHtmlAttributes())} />
            <img src="$src" />
EOT;
        return $html;
    }
    public function getUseDefaultHtml() {
        $html = <<<EOT
            <li class="scalable add m-button">
                <span>{$this->adminHelper()->__('Add')}</span>
            </li>
            <li class="scalable change m-button">
                <span>{$this->adminHelper()->__('Change')}</span>
            </li>
            <li class="scalable delete m-button">
                <span>{$this->adminHelper()->__('Remove')}</span>
            </li>

EOT;
        return $html;
    }

    protected function _getStyle() {
        return '';
    }
    #region Dependencies
    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }
    /**
     * @return Mana_Core_Helper_Files
     */
    public function fileHelper() {
        return Mage::helper('mana_core/files');
    }
    /**
     * @return Mana_Core_Helper_Js
     */
    public function jsHelper() {
        return Mage::helper('mana_core/js');
    }
    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }
    #endregion
}