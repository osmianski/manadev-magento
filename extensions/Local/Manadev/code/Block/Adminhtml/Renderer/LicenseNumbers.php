<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Adminhtml_Renderer_LicenseNumbers extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected function _getValue(Varien_Object $row) {
        $lines = explode("|", parent::_getValue($row));
        $html = "";
        if (is_array($lines)) {
            foreach ($lines as $x => $line) {
                if ($x == 5) {
                    $html .= "<div class='mana-multiline' style='display:none;'>";
                }

                $filter = 'm_license_no='.$line;
                $filter = base64_encode($filter);
                $url = Mage::helper('adminhtml')->getUrl('adminhtml/license/issuedLicenses', array('filter'=>$filter));

                $html .= "<a href='{$url}'>{$line}</a>";
                if(count($lines) != ($x + 1)) {
                    $html .= '<br/><br/>';
                }
            }
            if (count($lines) > 5) {
                $html .= "</div>";
                $html .= "<br/>";
                $html .= "<a href='#' class='mana-multiline-show-more'>" . Mage::helper('local_manadev')->__('Show More...') . "</a>";
                $html .= "<a href='#' class='mana-multiline-show-less' style='display:none;'>" . Mage::helper('local_manadev')->__('Show Less...') . "</a>";
            }
        }

        return $html;
    }
}