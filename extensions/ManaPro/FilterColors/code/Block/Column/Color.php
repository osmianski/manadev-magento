<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterColors
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class ManaPro_FilterColors_Block_Column_Color extends Mana_Admin_Block_Column_Color {
    protected function _getStyle($row) {
        if (!($color = $row->getData($this->getColumn()->getIndex()))) {
            $color = 'transparent';
        }
        $width = $this->getColumn()->getImageWidth();
        $height = $this->getColumn()->getImageHeight();
        $radius = $this->getColumn()->getImageBorderRadius();
        return
            "background: {$color}; width: {$width}px; height: {$height}px; ".
            "-webkit-border-radius: {$radius}px; -moz-border-radius: {$radius}px; border-radius: {$radius}px;";
    }
}