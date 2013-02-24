<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Renders one fieldset field including label, field itself and 'use default checkbox'
 * @author Mana Team
 *
 */
class Mana_Admin_Block_Field_Image extends Mana_Admin_Block_Crud_Card_Field {
    protected function _construct()
    {
        $this->setTemplate('mana/admin/field/image.phtml');
    }
    protected function _getStyle() {
        /* @var $files Mana_Core_Helper_Files */ $files = Mage::helper(strtolower('Mana_Core/Files'));
        if ($image = $this->getElement()->getValue()) {
            $image = 'background: url('.$files->getUrl($image, array('temp/image', 'image')).'); ';
        }
        $width = $height = '20px';

        return "{$image}width: {$width}; height: {$height}; ";
    }
}