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
        $order_numbers = array();

        $html = "";
        if(is_array($lines)) {
            $x = 0;
            foreach($lines as $line) {
                if($x == 5) {
                    $html .= "<div class='mana-multiline' style='display:none;'>";
                }
                if (!in_array($line, $order_numbers)) {
                    $url = $this->getUrl('adminhtml/sales_order/view', array('order_id' => $order_ids[$x]));
                    $html .= "<a href='{$url}'>{$line}</a>";
                    if (count($lines) != ($x + 1)) {
                        $html .= "<br/>";
                    }
                    $order_numbers[] = $line;
                    $x++;
                }
            }
            if(count($lines) > 5) {
                $html .= "</div>";
                $html .= "<br/>";
                $html .= "<a href='#' class='mana-multiline-show-more'>".Mage::helper('local_manadev')->__('Show More...')."</a>";
                $html .= "<a href='#' class='mana-multiline-show-less' style='display:none;'>" . Mage::helper('local_manadev')->__('Show Less...') . "</a>";
            }
        }

        return $html;
    }
}