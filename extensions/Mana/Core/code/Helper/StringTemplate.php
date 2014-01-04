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
class Mana_Core_Helper_StringTemplate extends Mage_Core_Helper_Abstract {
    public function parse($template) {
        $result = array();
        if (preg_match_all('/{{.*}}/', $template, $matches, PREG_OFFSET_CAPTURE)) {
            $lastOffset = 0;
            foreach ($matches[0] as $match) {
                list($var, $offset)  = $match;
                if ($offset > $lastOffset) {
                    $result[] = array('string', substr($template, $lastOffset, $offset - $lastOffset));
                }
                $result[] = array('var', substr($var, 2, strlen($var) - 4));
                $lastOffset = $offset + strlen($var);
            }
            $offset = strlen($template);
            if ($offset > $lastOffset) {
                $result[] = array('string', substr($template, $lastOffset, $offset - $lastOffset));
            }
        }
        else {
            $result[] = array('string', $template);
        }
        return $result;
    }

    public function dbConcat($parsedTemplate, $vars) {
        $result = array();
        foreach ($parsedTemplate as $token) {
            list($type, $text) = $token;
            if ($type == 'string') {
                $result[] = "'$text'";
            }
            elseif ($type == 'var') {
                if (isset($vars[$text])) {
                    $result[] = $vars[$text];
                }
                else {
                    $result[] = "'".'{{$text}}'."'";
                }
            }
        }
        return count($result) > 1
            ? 'CONCAT('.implode(', ', $result).')'
            : (count($result) ? $result[0] : "''");
    }
}