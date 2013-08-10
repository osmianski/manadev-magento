<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterAttributes
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_FilterAttributes_Resource_Review extends Mage_Review_Model_Mysql4_Review {
    public function aggregate($object)
    {
        parent::aggregate($object);
        Mage::getResourceSingleton('manapro_filterattributes/rating')->process($this,
            array('product_id' => $object->getData('entity_pk_value')));
    }
}