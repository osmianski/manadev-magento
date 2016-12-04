<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Adminhtml_Renderer_RegisteredDomain extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) {
        $htmlId = $this->_getHtmlId() . microtime(true);
        $registered_domain = $this->_getValue($row);
        $toggle1_style = "";
        $toggle2_style = "style='display:none'";
        if(trim($registered_domain) == "") {
            $toggle1_style = $toggle2_style;
            $toggle2_style = "";
        }

        $html = "<div>";

        $html .= "<div class='mana-toggle-1' $toggle1_style>";
        $html .= '  <input type="text" id="' . $htmlId . '" value="' . $registered_domain . '" class="input-text no-changes m-save-on-change m-registered-domain"/>';
        $html .= "</div>";

        $html .= "<div class='mana-toggle-2' $toggle2_style>";
        $lines = 1 + substr_count($row->getData('m_store_info'), "\n");
        $line_height = 15;
        $html .= '    <textarea row="2" class="m-store-info m-save-on-change" style="height:'. ($line_height *$lines) .'px">'.$row->getData('m_store_info').'</textarea>';
        $html .= "</div>";

        $html .= "<a href='#' class='mana-toggle-1-trigger' $toggle2_style>" . Mage::helper('local_manadev')->__('Show Registered URL') . "</a>";
        $html .= "<a href='#' class='mana-toggle-2-trigger' $toggle1_style>" . Mage::helper('local_manadev')->__('Show Store Information') . "</a>";
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