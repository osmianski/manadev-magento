<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Adminhtml_Renderer_Link extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected function _getValue(Varien_Object $row) {
        $html = parent::_getValue($row);

        if($linkData = $this->getColumn()->getLink()) {
            if(isset($linkData['route'])) {
                $params = $linkData['params'];
                foreach($params as $x => $param) {
                    preg_match('/\{\{\w+\}\}/', $param, $matches);
                    foreach($matches as $match) {
                        $col = trim(trim($matches[0], "{{"), "}}");
                        if($row->hasData($col)) {
                            $params[$x] = str_replace($match, $row->getData($col), $params[$x]);
                        }
                    }
                }

                $link = $this->getUrl($linkData['route'], $params);
            } else {
                $link = $linkData['link'];
                preg_match('/\{\{\w+\}\}/', $link, $matches);
                foreach($matches as $match) {
                    $col = trim(trim($matches[0], "{{"), "}}");
                    if ($row->hasData($col)) {
                        $link = str_replace($match, $row->getData($col), $link);
                    }
                }
            }

            $html = "<a href='".htmlentities($link)."'>".$html."</a>";
        }

        return $html;
    }
}