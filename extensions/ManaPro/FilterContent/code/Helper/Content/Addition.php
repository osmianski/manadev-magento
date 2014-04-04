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
class ManaPro_FilterContent_Helper_Content_Addition extends ManaPro_FilterContent_Helper_Content {
    protected $_key;
    protected $_addToKey;

    /**
     * @return bool|string
     */
    public function render() {
        $content = $this->rendererHelper()->render();
        return isset($content[$this->_key]) ? $content[$this->_key] : '';
    }

    public function getInitialContent($content) {
        $content[$this->_addToKey ? $this->_addToKey : $this->_key] = '';
        return $content;
    }

    /**
     * @param string $content
     * @param array $actions
     * @return string
     */
    public function processActions($content, $actions) {
        if (!empty($actions[$this->_key])) {
            $content[$this->_addToKey ? $this->_addToKey : $this->_key] .=
                trim($this->twigHelper()->renderStringCached($actions[$this->_key], $content,
                    $this->helper()->getTwigFilename($actions, $this->_key)));
        }
        return $content;
    }
}