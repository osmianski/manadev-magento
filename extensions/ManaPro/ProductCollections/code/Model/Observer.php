<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductCollections
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This class observes certain (defined in etc/config.xml) events in the whole system and provides public methods -
 * handlers for these events.
 * @author Mana Team
 *
 */
class ManaPro_ProductCollections_Model_Observer extends Mage_Core_Helper_Abstract {
    // left for compatibility
    public function preserveEntityIdFilters($observer) {
    }
}