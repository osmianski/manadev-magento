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
abstract class ManaPro_FilterContent_Helper_Content extends Mage_Core_Helper_Abstract {
    protected $_placeholder;
    protected $_initialContentReplacement;
    protected $_hasInitialContentReplacement = false;

    /**
     * @return bool|string
     */
    abstract public function render();

    /**
     * @param array $content
     * @return string
     */
    abstract public function getInitialContent($content);

    /**
     * @param string $value
     */
    public function replaceInitialContent($value) {
        $this->_initialContentReplacement = $value;
        $this->_hasInitialContentReplacement = true;
    }

    public function getPlaceholder() {
        return $this->_placeholder;
    }

    public function hasInitialContentReplacement() {
        return $this->_hasInitialContentReplacement;
    }

    public function getInitialContentReplacement() {
        return $this->_initialContentReplacement;
    }

    /**
     * @param string $content
     * @param array $actions
     * @return string
     */
    abstract public function processActions($content, $actions);

    #region Dependencies
    /**
     * @return ManaPro_FilterContent_Helper_Renderer
     */
    public function rendererHelper() {
        return Mage::helper('manapro_filtercontent/renderer');
    }

    /**
     * @return ManaPro_FilterContent_Helper_Data
     */
    public function helper() {
        return Mage::helper('manapro_filtercontent');
    }

    /**
     * @return Mana_Twig_Helper_Data
     */
    public function twigHelper() {
        return Mage::helper('mana_twig');
    }


    #endregion
}