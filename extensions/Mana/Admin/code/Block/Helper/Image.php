<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class Mana_Admin_Block_Helper_Image extends Mage_Adminhtml_Block_Template {
    protected function _prepareLayout() {
        /* @var $js Mana_Core_Helper_Js */ $js = Mage::helper(strtolower('Mana_Core/Js'));
        /* @var $files Mana_Core_Helper_Files */ $files = Mage::helper(strtolower('Mana_Core/Files'));
        $js->options('#m-image-helper', array(
            'baseUrl' => $files->getBaseUrl('image'),
            'uploadUrl' => $this->getUrl('*/upload/start')
        ));
        return parent::_prepareLayout();
    }


}