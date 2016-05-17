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

        /** @var Local_Manadev_Resource_DomainHistory_Collection $dhCollection */
        $dhCollection = Mage::getResourceModel('local_manadev/domainHistory_collection');
        $dhCollection->addFieldToFilter('item_id', $row->getData('item_id'))
            ->setOrder('created_at')->load();

        if($dhCollection->count() > 1) {
            $html .= "<br/><br/>";
            $html .= "<a href='#' class='mana-multiline-show-more'>" . Mage::helper('local_manadev')->__('Show Previous URLs...') . "</a>";
            $html .= "<a href='#' class='mana-multiline-show-less' style='display:none;'>" . Mage::helper('local_manadev')->__('Hide Previous URLs...') . "</a>";
            $html .= "<div class='mana-multiline' style='display:none;'>";

            /** @var Local_Manadev_Model_DomainHistory $dh */
            foreach($dhCollection->getItems() as $dh) {
                $html .= $dh->getData('m_registered_domain');
                $html .= "<br/>";
            }

            $html .= "</div>";
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
}