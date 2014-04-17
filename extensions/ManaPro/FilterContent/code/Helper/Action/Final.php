<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterContent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterContent_Helper_Action_Final extends Mage_Core_Helper_Abstract {
    protected $_value;

    /**
     * @return array
     */
    public function read() {
        if (!$this->_value) {
            $this->_value = array(
                'is_active' => true,
                'stop_further_processing' => true,
                'layout_xml' => '',

                'meta_title' => Mage::getStoreConfig('mana_filtercontent/final/meta_title'),
                'meta_keywords' => Mage::getStoreConfig('mana_filtercontent/final/meta_keywords'),
                'meta_description' => Mage::getStoreConfig('mana_filtercontent/final/meta_description'),
                'meta_robots' => Mage::getStoreConfig('mana_filtercontent/final/meta_robots'),

                'title' => Mage::getStoreConfig('mana_filtercontent/final/title'),
                'subtitle' => Mage::getStoreConfig('mana_filtercontent/final/subtitle'),
                'description' => Mage::getStoreConfig('mana_filtercontent/final/description'),
                'additional_description' => '',
                'common_directives' => '',
                'background_image' => '',
                'cache_key' => 'config/final',
            );
        }
        return $this->_value;
    }

}