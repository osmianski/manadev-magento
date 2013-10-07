<?php
/**
 * @category    Mana
 * @package     Mana_GeoLocation
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_GeoLocation_LocationController extends Mage_Core_Controller_Front_Action {
    public function countryAction() {
        try {
            $result = array('countryId' => Mage::helper('mana_geolocation')->find($this->getRequest()->getParam('email')));
            if ($result['countryId'] == 'ZZ' || !$result['countryId']) {
                $result['countryId'] = Mage::getStoreConfig('general/country/default');
            }
        }
        catch (Mage_Core_Exception $e) {
            $result = array('error' => $e->getMessage());
        }
        catch (Exception $e) {
            $result = array('error' => $this->__('Server error'));
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}