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
class ManaPro_FilterColors_Block_Column_Image extends Mana_Admin_Block_Column_Image {
    protected function _getStyle($row) {
        /* @var $files Mana_Core_Helper_Files */ $files = Mage::helper(strtolower('Mana_Core/Files'));
        if ($image = $row->getData($this->getColumn()->getIndex())) {
            $image = 'background: url('.$files->getUrl($image, array('temp/image', 'image')).'); ';
        }
        $width = $this->getColumn()->getImageWidth();
        $height = $this->getColumn()->getImageHeight();
        $radius = $this->getColumn()->getImageBorderRadius();
        return
            "{$image}width: {$width}px; height: {$height}px; ".
            "-webkit-border-radius: {$radius}px; -moz-border-radius: {$radius}px; border-radius: {$radius}px;";
    }
}