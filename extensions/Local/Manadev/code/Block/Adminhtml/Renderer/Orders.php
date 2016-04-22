<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Adminhtml_Renderer_Orders extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected function _getValue(Varien_Object $row) {
        $lines = explode("|", parent::_getValue($row));
        $order_ids = explode("|", $row->getData('order_ids'));

        $html = "";
        if(is_array($lines)) {
            foreach($lines as $x => $line) {
                if($x == 5) {
                    $html .= "<div class='mana-multiline' style='display:none;'>";
                }
                $url = $this->getUrl('adminhtml/sales_order/view', array('order_id' => $order_ids[$x]));

                $html .= "<a href='{$url}'>{$line}</a> <br/>";
            }
            if(count($lines) >= 5) {
                $html .= "</div>";
                $html .= "<a href='#' class='mana-multiline-show-more'>".Mage::helper('local_manadev')->__('Show More...')."</a>";
                $html .= "<a href='#' class='mana-multiline-show-less' style='display:none;'>" . Mage::helper('local_manadev')->__('Show Less...') . "</a>";
            }
        }

        return $html;
    }
}