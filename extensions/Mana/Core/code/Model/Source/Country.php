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
class Mana_Core_Model_Source_Country extends Mana_Core_Model_Source_Abstract {
    protected function _getAllOptions() {
        return Mage::getResourceModel('directory/country_collection')->load()->toOptionArray();
    }
}