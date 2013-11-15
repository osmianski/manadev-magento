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
class Mana_AttributePage_Model_AttributePage_Store extends Mana_AttributePage_Model_AttributePage_Abstract {
    const ENTITY = 'mana_attributepage/attributePage_store';

    protected function _construct() {
        $this->_init(self::ENTITY);
    }

    public function canShow() {
        return $this->getData('is_active');
    }

    public function loadByOptionPageStoreId($optionPageStoreId) {
        if ($id = $this->getResource()->getIdByOptionPageStoreId($optionPageStoreId)) {
            $this->load($id);
        }

        return $this;
    }

    /**
     * @return Mana_AttributePage_Resource_AttributePage_Store
     */
    public function getResource() {
        return parent::getResource();
    }

}