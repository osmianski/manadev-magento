<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Adminhtml_Renderer_RegisteredDomain extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) {
        $htmlId = $this->_getHtmlId() . microtime(true);
        $html = "";

        $html .= "<div>";

        $html .= "<div class='mana-toggle-1'>";
        $html .= '  <input type="text" id="' . $htmlId . '" value="' . $this->_getValue($row) . '" class="input-text no-changes m-save-on-change m-registered-domain"/>';
        $html .= "</div>";
        $html .= "<div class='mana-toggle-2' style='display:none'>";
        $html .= '    <textarea row="2" class="m-store-info m-save-on-change">'.$row->getData('m_store_info').'</textarea>';
        $html .= "</div>";

        $html .= "<a href='#' class='mana-toggle-1-trigger' style='display:none;'>" . Mage::helper('local_manadev')->__('Show Registered URL') . "</a>";
        $html .= "<a href='#' class='mana-toggle-2-trigger'>" . Mage::helper('local_manadev')->__('Show Store Information') . "</a>";
        $html .= "</div>";

        $dhCollection = $this->localHelper()->prepareDomainHistoryCollection($row->getData('item_id'));

        $html .= $this->localHelper()->getDomainHistoryHtml($dhCollection);

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