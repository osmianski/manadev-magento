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
    const RENDER_ALL = '_renderAllContent';
    const RENDER_ECHO_TAGS = '_renderEchoTagContent';
    protected $_actionKey;
    protected $_contentKey;

    /**
     * @param Mage_Core_Model_Config_Element $xml
     */
    public function init($xml) {
        $this->_actionKey = $xml->getName();
        $this->_contentKey = isset($xml->content_key) ? (string)$xml->content_key : $this->_actionKey;
    }

    /**
     * @return bool|string
     */
    public function render() {
        return $this->_renderEchoTagContent($this->rendererHelper()->render());
    }

    /**
     * @param string $content
     * @param array $actions
     * @return string
     */
    public function processActions($content, $actions) {
        if (isset($actions[$this->_actionKey]) && $this->_isChanged($actions[$this->_actionKey])) {
            $template = '{% spaceless %}<echo>' . $actions[$this->_actionKey] . '<echo>{% endspaceless %}';
            $filename = $this->helper()->getTwigFilename($actions, $this->_actionKey);
            $content[$this->_contentKey] = trim($this->twigHelper()->renderStringCached($template, $content, $filename));
        }
        return $content;
    }

    protected function _renderEchoTagContent($content) {
        if ($this->_actionKey == $this->_contentKey) {
            return isset($content[$this->_contentKey]) ? str_replace('<echo>', '', str_replace('</echo>', '', $content[$this->_contentKey])) : false;
        }
        else {
            return false;
        }
    }

    protected function _renderAllContent() {
        $content = $this->rendererHelper()->render();
        return isset($content[$this->_actionKey]) ? $content[$this->_actionKey] : '';
    }

    protected function _isChanged($template) {
        $template = trim($template);
        if (preg_match('/{{\s*'.$this->_actionKey.'\s*}}/', $template, $matches)) {
            return $template != $matches[0];
        } else {
            return true;
        }
    }

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