<?php
/** 
 * @category    Mana
 * @package     Mana_AttributePage
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_AttributePage_Model_Layer {
    /**
     * @return Mana_AttributePage_Resource_Mysql_Layer
     */
    protected function _getResource() {
        if (Mage::helper('mana_attributepage')->useSolrForNavigation()) {
            return Mage::getResourceSingleton('mana_attributepage/solr_layer');
        }
        else {
            return Mage::getResourceSingleton('mana_attributepage/mysql_layer');
        }
    }

    public function apply() {
        $this->_getResource()->apply();
    }
}