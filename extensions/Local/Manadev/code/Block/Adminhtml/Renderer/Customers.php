<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Adminhtml_Renderer_Customers extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected function _getValue(Varien_Object $row) {
        $lines = explode("|", parent::_getValue($row));
        $customer_ids = explode("|", $row->getData('customer_ids'));
        $customer_names = array();

        $html = "";
        if(is_array($lines)) {
            $x = 0;
            foreach($lines as $y => $line) {
                if($x == 5) {
                    $html .= "<div class='mana-multiline' style='display:none;'>";
                }

                if(!in_array($line, $customer_names)) {
                    if($customer_ids[$y] != "") {
                        $url = $this->getUrl('adminhtml/customer/edit', array('id' => $customer_ids[$y]));
                        $html .= "<a href='{$url}'>{$line}</a>";
                    } else {
                        $html .= $line;
                    }
                    if (count($lines) != ($x + 1)) {
                        $html .= "<br/>";
                    }
                    $customer_names[] = $line;
                    $x++;
                }
            }
            if($x >= 5) {
                $html .= "</div>";
                $html .= "<br/>";
                $html .= "<a href='#' class='mana-multiline-show-more'>".Mage::helper('local_manadev')->__('Show More...')."</a>";
                $html .= "<a href='#' class='mana-multiline-show-less' style='display:none;'>" . Mage::helper('local_manadev')->__('Show Less...') . "</a>";
            }
        }

        return $html;
    }
}