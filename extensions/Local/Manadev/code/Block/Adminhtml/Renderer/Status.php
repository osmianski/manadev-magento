<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Adminhtml_Renderer_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected function _getValue(Varien_Object $row) {
        $model = $this->_getDownloadStatusModel();

        return $model->getStatusLabel($row['status']);
    }

    public function render(Varien_Object $row) {
        $name = $this->getColumn()->getName() ? $this->getColumn()->getName() : $this->getColumn()->getId();
        $html = '<select class="m-save-on-change m-status" name="' . $this->escapeHtml($name) . '" ' . $this->getColumn()->getValidateClass() . '>';
        $value = $row->getData($this->getColumn()->getIndex());

        $statusModel = $this->_getDownloadStatusModel()->toOptionArray();
        $product_id = $row['product_id'];

        $platform = Mage::getResourceModel('catalog/product')->getAttributeRawValue($product_id, 'platform', 0);

        if($platform == Local_Manadev_Model_Platform::VALUE_MAGENTO_2) {
            $options = array(
                Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE_TIL => $statusModel[Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE_TIL],
                Local_Manadev_Model_Download_Status::M_LINK_STATUS_DOWNLOAD_EXPIRED => $statusModel[Local_Manadev_Model_Download_Status::M_LINK_STATUS_DOWNLOAD_EXPIRED],
                Local_Manadev_Model_Download_Status::M_LINK_STATUS_NOT_REGISTERED => $statusModel[Local_Manadev_Model_Download_Status::M_LINK_STATUS_NOT_REGISTERED],
                Local_Manadev_Model_Download_Status::M_LINK_STATUS_NOT_AVAILABLE => $statusModel[Local_Manadev_Model_Download_Status::M_LINK_STATUS_NOT_AVAILABLE],
            );
        } else {
            $options = array(
                Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE => $statusModel[Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE],
                Local_Manadev_Model_Download_Status::M_LINK_STATUS_PERIOD_EXPIRED => $statusModel[Local_Manadev_Model_Download_Status::M_LINK_STATUS_PERIOD_EXPIRED],
                Local_Manadev_Model_Download_Status::M_LINK_STATUS_NOT_REGISTERED => $statusModel[Local_Manadev_Model_Download_Status::M_LINK_STATUS_NOT_REGISTERED],
                Local_Manadev_Model_Download_Status::M_LINK_STATUS_NOT_AVAILABLE => $statusModel[Local_Manadev_Model_Download_Status::M_LINK_STATUS_NOT_AVAILABLE],
            );
        }

        foreach ($options as $val => $label) {
            $selected = (($val == $value && (!is_null($value))) ? ' selected="selected"' : '');
            $html .= '<option value="' . $this->escapeHtml($val) . '"' . $selected . '>';
            $html .= $this->escapeHtml($label) . '</option>';
        }
        $html .= '</select>';

        return $html;
    }

    /**
     * @return Local_Manadev_Model_Download_Status
     */
    protected function _getDownloadStatusModel() {
        $model = Mage::getSingleton('local_manadev/download_status');

        return $model;
    }
}