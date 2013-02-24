<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * Modified stock item behavior
 * @author Mana Team
 *
 */
class ManaPro_ProductFaces_Model_Item extends Mage_CatalogInventory_Model_Stock_Item {
    public function checkQty($qty)
    {
        if (!$this->getManageStock() || Mage::app()->getStore()->isAdmin()) {
            return true;
        }
        // MANA BEGIN
        if ($this->getMRepresents()) {
        	$notEnough = ($this->getMRepresentedQty() - $qty < 0);
        }
        else {
        	$notEnough = ($this->getQty() - $qty < 0);
        }
        // MANA END
        if ($notEnough) {
            switch ($this->getBackorders()) {
                case Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NONOTIFY:
                case Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY:
                    break;
                default:
                    return false;
                    break;
            }
        }
        return true;
    }
}