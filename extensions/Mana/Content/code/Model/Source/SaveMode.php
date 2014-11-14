<?php
/** 
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Content_Model_Source_SaveMode extends Mana_Core_Model_Source_Abstract  {

    /**
     * @return array
     */
    protected function _getAllOptions() {
        return array(
            array('value' => 'all', 'label' => $this->helper()->__('Save all modified pages')),
            array('value' => 'page', 'label' => $this->helper()->__('Save each page when opening another page for editing')),
            array('value' => 'field', 'label' => $this->helper()->__('Save after editing any field')),
        );
    }

    /**
     * @return Mana_Content_Helper_Data
     */
    protected  function helper() {
        return Mage::helper('mana_content');
    }
}