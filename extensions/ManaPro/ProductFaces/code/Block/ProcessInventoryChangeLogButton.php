<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class ManaPro_ProductFaces_Block_ProcessInventoryChangeLogButton extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('manapro/productfaces/process_inventory_change_log_button.phtml');
        }
        return $this;
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $originalData = $element->getOriginalData();
        $label = Mage::helper('manapro_productfaces')->__($originalData['button_label']);

        /* @var ManaPro_ProductFaces_Model_ChangeLog $changeLog */
        $changeLog = Mage::getSingleton('manapro_productfaces/changeLog');
        if ($changeLog->isEnabled()) {
            $label .= " ({$changeLog->getPendingProductCount()})";
        }

        $this->addData(array(
            'button_label' => $label,
            'html_id' => $element->getHtmlId(),
            'button_url' => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/representing_products/processInventoryChangeLog')
        ));

        return $this->_toHtml();
    }
}