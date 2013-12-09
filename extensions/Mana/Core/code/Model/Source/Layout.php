<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Core_Model_Source_Layout extends Mana_Core_Model_Source_Abstract
{
    protected function _getAllOptions()
    {
        $result = Mage::getSingleton('page/source_layout')->toOptionArray();
        array_unshift($result, array('value'=>'', 'label'=>Mage::helper('catalog')->__('No layout updates')));
        return $result;
    }
}
