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
class Mana_AttributePage_Model_OptionPage_Store extends Mana_AttributePage_Model_OptionPage_Abstract {
    const ENTITY = 'mana_attributepage/optionPage_store';

    protected function _construct() {
        $this->_init(self::ENTITY);
    }

    public function canShow() {
        return $this->getData('is_active');
    }

    public function getUrl() {
        return Mage::getUrl('mana/optionPage/view', array(
            'id' => $this->getId(),
            '_use_rewrite' => true,
            '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure(),
        ));
    }
}