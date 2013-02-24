<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterColors
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Renders one fieldset field including label, field itself and 'use default checkbox'
 * @author Mana Team
 *
 */
class ManaPro_FilterColors_Block_Field_Image extends Mana_Admin_Block_Field_Image {
    protected function _construct()
    {
        $this->setTemplate('mana/admin/field/image.phtml');
    }
    protected function _getStyle() {
        /* @var $files Mana_Core_Helper_Files */ $files = Mage::helper(strtolower('Mana_Core/Files'));
        if ($image = $this->getElement()->getValue()) {
            $image = 'background: url('.$files->getUrl($image, array('temp/image', 'image')).'); ';
        }
        $width = $this->getElement()->getImageWidth();
        $height = $this->getElement()->getImageHeight();
        $radius = $this->getElement()->getImageBorderRadius();
        return
            "{$image}width: {$width}px; height: {$height}px; ".
            "-webkit-border-radius: {$radius}px; -moz-border-radius: {$radius}px; border-radius: {$radius}px;";
    }
}