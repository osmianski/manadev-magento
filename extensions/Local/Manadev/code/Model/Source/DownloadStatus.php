<?php
/**
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Source for options of filter being filterable
 * @author Mana Team
 *
 */
class Local_Manadev_Model_Source_DownloadStatus extends Mana_Core_Model_Source_Abstract {
	protected function _getAllOptions() {
		return array(
            array('value' => 'n_a', 'label' => Mage::helper('local_manadev')->__('N/A')),
            array('value' => 'available', 'label' => Mage::helper('local_manadev')->__('Available')),
            array('value' => 'not_available', 'label' => Mage::helper('local_manadev')->__('Not Available')),
            array('value' => 'partially_available', 'label' => Mage::helper('local_manadev')->__('Partially Available')),
        );
	}
}