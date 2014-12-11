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
class ManaPro_FilterContent_Model_Rule_Abstract extends Mage_Rule_Model_Rule {
    public function getConditionsInstance() {
        return Mage::getModel('manapro_filtercontent/condition_combine');
    }
}