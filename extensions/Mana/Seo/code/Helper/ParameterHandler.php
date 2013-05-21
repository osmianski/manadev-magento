<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
abstract class Mana_Seo_Helper_ParameterHandler extends Mage_Core_Helper_Abstract {
    /**
     * @param Mana_Seo_Model_Context $context
     * @param Mana_Seo_Model_Url[] $activeParameterUrls
     * @param Mana_Seo_Model_Url[] $obsoleteParameterUrls
     * @return Mana_Seo_Helper_ParameterHandler
     */
    abstract public function getParameterUrls($context, &$activeParameterUrls, &$obsoleteParameterUrls);

    /**
     * @return $this
     */
    public function prepareForParameterEncoding() {
        return $this;
    }

    /**
     * @param array $parameters
     * @return array
     */
    public function getParameterPositions(/** @noinspection PhpUnusedParameterInspection */ $parameters) {
        return array();
    }

    /**
     * @param string $parameter
     * @param string $value
     * @return bool
     */
    public function encodeParameter(/** @noinspection PhpUnusedParameterInspection */ $parameter, $value) {
        return false;
    }
}