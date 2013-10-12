<?php
/** 
 * @category    Mana
 * @package     Mana_CatalogInventory
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_CatalogInventory_Resource_Stock extends Mage_CatalogInventory_Model_Mysql4_Stock {
    public function correctItemsQty($stock, $productQtys, $operator = '-')
    {
        if ($this->helper()->isManadevProductFacesInstalled()) {
            Mage::getResourceSingleton('manapro_productfaces/stock')->correctItemsQty($stock, $productQtys, $operator);
        }
        else {
            parent::correctItemsQty($stock, $productQtys, $operator);
        }
        return $this;
    }

    /**
     * @return Mana_CatalogInventory_Helper_Data
     */
    public function helper()
    {
        return Mage::helper('mana_cataloginventory');
    }

}