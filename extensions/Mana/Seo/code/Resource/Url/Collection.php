<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Seo_Resource_Url_Collection extends Mana_Db_Resource_Entity_Collection {
    /**
     * @param string | array $types
     * @return $this
     */
    public function addTypeFilter($types) {
        /* @var $seo Mana_Seo_Helper_Data */
        $seo = Mage::helper('mana_seo');

        if (!is_array($types)) {
            $types = array($types);
        }

        $types = $seo->getUrlTypes($types);

        if (count($types) == 0) {
            $this->getSelect()->where(new Zend_Db_Expr('1 <> 1'));
        }
        elseif (count($types) == 1) {
            $this->addFieldToFilter('type', $types[0]);
        }
        else {
            $this->addFieldToFilter('type', array('in' => $types));
        }

        return $this;
    }
}