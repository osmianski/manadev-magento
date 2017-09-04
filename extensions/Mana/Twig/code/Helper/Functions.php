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

    /**
     * @param $context
     * @param array $expected
     * @return bool
     */
    public function filters_are($context, $expected) {
        if (!is_array($expected)) {
            return false;
        }

        foreach ($context['filters'] as $option) {
            /* @var Mana_Filters_Model_Item $option */
            $code = $option->getFilter()->getFilterOptions()->getCode();
            if (!isset($expected[$code])) {
                return false;
            }

            if (is_array($expected[$code])) {
                if (($pos = array_search($option->getLabel(), $expected[$code]))!== false) {
                    array_splice($expected[$code], $pos, 1);
                }
                elseif (($pos = array_search($option->getValue(), $expected[$code])) !== false) {
                    array_splice($expected[$code], $pos, 1);
                }
                else {
                    return false;
                }

                if (count($expected[$code]) == 0) {
                    unset($expected[$code]);
                }
            }
            else {
                if ($option->getLabel() != $expected[$code] && $option->getValue() != $expected[$code]) {
                    return false;
                }

                unset($expected[$code]);
            }
        }

        return count($expected) == 0;
    }
}