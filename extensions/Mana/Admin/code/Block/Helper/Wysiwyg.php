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
class Mana_Admin_Block_Helper_Wysiwyg extends Mage_Adminhtml_Block_Template {
    protected function _prepareLayout() {
        /* @var $js Mana_Core_Helper_Js */ $js = Mage::helper(strtolower('Mana_Core/Js'));
        /* @var $admin Mana_Admin_Helper_Data */ $admin = Mage::helper(strtolower('Mana_Admin'));
        $js->options('#mana-wysiwyg-editor', array(
            'url' => $this->getUrl('*/catalog_product/wysiwyg'),
            'storeId' => $admin->getStore()->getId(),
        ));
        return parent::_prepareLayout();
    }


}