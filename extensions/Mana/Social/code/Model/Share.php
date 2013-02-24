<?php
/**
 * @category    Mana
 * @package     Mana_Social
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method string getCode()
 * @method Mana_Social_Model_Share setCode(string $value)
 * @method string getBlock()
 * @method Mana_Social_Model_Share setBlock(string $value)
 * @method string getBlockProduct()
 * @method Mana_Social_Model_Share setBlockProduct(string $value)
 * @method Mana_Social_Model_Site_Abstract getSite()
 * @method Mana_Social_Model_Share setSite(Mana_Social_Model_Site_Abstract $value)
 */
class Mana_Social_Model_Share extends Varien_Object
{
    public function getFullCode() {
        return "{$this->getSite()->getCode()}_{$this->getCode()}";
    }
}