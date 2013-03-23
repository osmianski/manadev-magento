<?php
/** 
 * @category    Mana
 * @package     Mana_Theme
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Theme_Model_Design_Package extends Mage_Core_Model_Design_Package {
    const MANA_BASE_PACKAGE = 'm-base';
    const MANA_DEFAULT_THEME = 'default';

    protected function _fallback($file, array &$params, array $fallbackScheme = array(array()))
    {
        if ($this->_shouldFallback) {
            foreach ($fallbackScheme as $try) {
                $params = array_merge($params, $try);
                $filename = $this->validateFile($file, $params);
                if ($filename) {
                    return $filename;
                }
            }

            $params['_package'] = self::MANA_BASE_PACKAGE;
            $params['_theme']   = self::MANA_DEFAULT_THEME;
            $filename = $this->validateFile($file, $params);
            if ($filename) {
                return $filename;
            }

            $params['_package'] = self::BASE_PACKAGE;
            $params['_theme']   = self::DEFAULT_THEME;
        }
        return $this->_renderFilename($file, $params);
    }
}