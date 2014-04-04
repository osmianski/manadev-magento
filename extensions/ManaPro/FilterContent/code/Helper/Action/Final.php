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
class ManaPro_FilterContent_Helper_Action_Final extends ManaPro_FilterContent_Helper_Action {
    protected $_value;

    /**
     * @return array
     */
    public function read() {
        if (!$this->_value) {
            $this->_value = $this->_normalize(array(
                'layout_xml' => '',
                'widget_layout_xml' => '',
                'meta_title' => Mage::getStoreConfig('mana_filtercontent/final/meta_title'),
                'meta_keywords' => Mage::getStoreConfig('mana_filtercontent/final/meta_keywords'),
                'cache_key' => 'config/final',
            ));
        }
        return $this->_value;
    }

}