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
abstract class ManaPro_FilterContent_Helper_Content_Replacement extends ManaPro_FilterContent_Helper_Content {
    protected $_key;

    /**
     * @return bool|string
     */
    public function render() {
        $content = $this->rendererHelper()->render();
        return isset($content[$this->_key])
            ? str_replace('<text>', '', str_replace('</text>', '', $this->rendererHelper()->replacePlaceholders($content[$this->_key])))
            : false;
    }

    /**
     * @param array $content
     * @return string
     */
    public function getInitialContent($content) {
        $content[$this->_key] = $this->_placeholder;
        $content['page_' . $this->_key] = $this->_placeholder;
        return $content;

    }

    public function processActions($content, $actions) {
        if ($this->_isChanged($actions[$this->_key])) {
            $content[$this->_key] = trim($this->twigHelper()->renderStringCached($actions[$this->_key], $content,
                $this->helper()->getTwigFilename($actions, $this->_key)));
        }
        return $content;
    }

    protected function _isChanged($template) {
        $template = trim($template);
        if (preg_match('/{{\s*meta_title\s*}}/', $template, $matches)) {
            return $template != $matches[0];
        } else {
            return true;
        }
    }

}