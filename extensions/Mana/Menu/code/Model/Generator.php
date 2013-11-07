<?php
/** 
 * @category    Mana
 * @package     Mana_Menu
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
abstract class Mana_Menu_Model_Generator {
    /**
     * @param Mage_Core_Model_Config_Element $element
     */
    abstract public function extend($element);
}