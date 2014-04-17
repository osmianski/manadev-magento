<?php
/**
 * @category    Mana
 * @package     Mana_CatalogInventory
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/* BASED ON SNIPPET: New Module/Helper/Data.php */
/**
 * Generic helper functions for Mana_CatalogInventory module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class Mana_CatalogInventory_Helper_Data extends Mage_Core_Helper_Abstract {

    public function isManadevProductFacesInstalled()
    {
        return $this->isModuleEnabled('ManaPro_ProductFaces');
    }

    public function isManadevFilterAttributesInstalled()
    {
        return $this->isModuleEnabled('ManaPro_FilterAttributes');
    }

}