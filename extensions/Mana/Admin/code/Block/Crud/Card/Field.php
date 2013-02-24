<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Renders one fieldset field including label, field itself and 'use default checkbox'
 * @author Mana Team
 *
 */
class Mana_Admin_Block_Crud_Card_Field extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element {
    protected function _construct()
    {
        $this->setTemplate('mana/admin/field.phtml');
    }
	
    public function getDefaultLabel() {
    	return $this->getElement()->getDefaultLabel();
	}
	public function getUsedDefault() {
    	return !Mage::helper('mana_db')->hasOverriddenValue(
    		$this->getElement()->getForm()->getModel(),
    		null,  
    		$this->getElement()->getDefaultBit());
	}
    public function checkFieldDisable()
    {
        if ($this->getDisplayUseDefault() && $this->getUsedDefault()) {
            $this->getElement()->setDisabled(true);
        }
        return $this;
    }
    public function getDisplayUseDefault()
    {
    	return $this->getElement()->hasDefaultBit();
    }
    public function getUseDefaultEnabled()
    {
    	return !$this->getElement()->getIsDefaultDisabled();
    }
}