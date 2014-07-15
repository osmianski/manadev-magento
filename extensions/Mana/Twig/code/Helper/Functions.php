<?php
/** 
 * @category    Mana
 * @package     Mana_Twig
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Twig_Helper_Functions extends Mage_Core_Helper_Abstract {
    public function remove($items, $field, $value) {
        $keys = array();
        if ($items) {
            foreach ($items as $key => $item) {
                if (isset($item[$field]) && $item[$field] == $value) {
                    $keys[] = $key;
                }
            }
        }
        foreach (array_reverse($keys) as $key) {
            unset($items[$key]);
        }
        return $items;
    }
}