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
class Mana_AttributePage_Model_AttributePage_Global extends Mana_AttributePage_Model_AttributePage_Abstract {
    const ENTITY = 'mana_attributepage/attributePage_global';

    protected function _construct() {
        $this->_init(self::ENTITY);
    }

    public function setDefaults() {
        $this->getResource()->setDefaults($this);
        return $this;
    }

    /**
     * Retrieve model resource
     *
     * @return Mana_AttributePage_Resource_AttributePage_Global
     */
    public function getResource()
    {
        return parent::getResource();
    }
}