<?php
/** 
 * @category    Mana
 * @package     ManaPro_Guestbook
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Guestbook_Block_Form extends Mage_Core_Block_Template {
    protected function _prepareLayout() {
        foreach (Mage::helper('manapro_guestbook')->getVisibleFields() as $field) {
            $this->setChild($field, $this->getLayout()->createBlock('core/template', '')
                ->setTemplate("manapro/guestbook/form/{$field}.phtml"));
        }
        return parent::_prepareLayout();
    }
}