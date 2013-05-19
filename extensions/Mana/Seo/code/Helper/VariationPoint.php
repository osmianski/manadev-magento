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
abstract class Mana_Seo_Helper_VariationPoint extends Mage_Core_Helper_Abstract {
    /**
     * @param string $haystack
     * @param string $sep
     * @param bool $throwIfSepEmpty
     * @throws Exception
     * @return string[]
     */
    protected function _parsePath($haystack, $sep, $throwIfSepEmpty = true) {
        if ($sep) {
            /* @var $mbstring Mana_Core_Helper_Mbstring */
            $mbstring = Mage::helper('mana_core/mbstring');

            if (!is_array($sep)) {
                $sep = array($sep);
            }

            $pos = 0;
            $parsed = false;
            $candidates = array();
            while (!$parsed) {
                $nextPos = $pos;
                $parsed = true;
                foreach ($sep as $value) {
                    if (($sepPos = $mbstring->strpos($haystack, $value, $pos)) != false && $sepPos > $nextPos) {
                        $nextPos = $sepPos;
                        $parsed = false;
                    }
                }
                if (!$parsed) {
                    $candidates[] = $mbstring->substr($haystack, 0, $nextPos);
                    $pos = $nextPos + 1;
                }
            }
            $candidates[] = $haystack;

            return $candidates;
        }
        else {
            if ($throwIfSepEmpty) {
                throw new Exception(Mage::helper('mana_seo')->__("Path separator can't be empty"));
            }
            else {
                return array($haystack);
            }
        }
    }

    /**
     * @param Mana_Seo_Model_Context $context
     * @return bool
     */
    abstract public function match($context);
}