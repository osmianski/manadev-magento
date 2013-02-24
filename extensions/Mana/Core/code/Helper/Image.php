<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * @author Mana Team
 *
 */
class Mana_Core_Helper_Image extends Mage_Catalog_Helper_Image {
    protected function setImageFile($file) {
        $this->_getModel()->setBaseFile($file);
        return parent::setImageFile($file);
    }
}