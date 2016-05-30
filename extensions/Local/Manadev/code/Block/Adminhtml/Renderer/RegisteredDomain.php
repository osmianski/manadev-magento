<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Adminhtml_Renderer_RegisteredDomain extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) {
        $htmlId = $this->_getHtmlId() . microtime(true);


        $html = '<input type="text" id="' . $htmlId . '" value="' . $this->_getValue($row) . '" class="input-text no-changes m-save-on-change m-registered-domain"/>';

        $dhCollection = $this->localHelper()->prepareDomainHistoryCollection($row->getData('item_id'));

        if($dhCollection->count() > 1) {
            $html .= $this->localHelper()->getDomainHistoryHtml($dhCollection);
        }



        return $html;
    }

    /**
     * Retrieve html id of filter
     *
     * @return string
     */
    protected function _getHtmlId()
    {
        return $this->getColumn()->getGrid()->getId() . '_'. $this->getColumn()->getId();
    }

    /**
     * @return Local_Manadev_Model_Download_Status
     */
    protected function _getDownloadStatusModel() {
        $model = Mage::getSingleton('local_manadev/download_status');

        return $model;
    }

    /**
     * @return Local_Manadev_Helper_Data
     */
    protected function localHelper() {
        return Mage::helper('local_manadev');
    }
}