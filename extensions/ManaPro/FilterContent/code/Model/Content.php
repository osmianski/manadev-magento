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
class ManaPro_FilterContent_Model_Content {
    const RENDER_ALL = '_renderAllContent';
    const RENDER_ECHO_TAGS = '_renderEchoTagContent';

    const INSTRUCTION_REPLACE = 'replace';
    const INSTRUCTION_ADD = 'add';
    const INSTRUCTION_PRE_PROCESS = 'pre_process';
    const INSTRUCTION_POST_PROCESS = 'post_process';

    protected $_key;
    protected $_instruction = 'replace';
    protected $_isSpaceSensitive = false;

    /**
     * @param Mage_Core_Model_Config_Element $xml
     */
    public function init($xml) {
        $this->_key = $xml->getName();
        if (!empty($xml->instruction)) {
            $this->_instruction = (string)$xml->instruction;
        }
        if (!empty($xml->is_space_sensitive)) {
            $this->_isSpaceSensitive = (bool)(string)$xml->is_space_sensitive;
        }
    }

    public function isContentAdded() {
        return $this->_instruction == self::INSTRUCTION_ADD;
    }

    public function isContentReplaced() {
        return $this->_instruction == self::INSTRUCTION_REPLACE;
    }

    public function isContentPreProcessed() {
        return $this->_instruction == self::INSTRUCTION_PRE_PROCESS;
    }

    public function isContentPostProcessed() {
        return $this->_instruction == self::INSTRUCTION_POST_PROCESS;
    }

    public function isSpaceSensitive() {
        return $this->_isSpaceSensitive;
    }
    public function isChanged($template) {
        $template = trim($template);
        if (preg_match('/{{\s*'.$this->_key.'\s*}}/', $template, $matches)) {
            return $template != $matches[0];
        } else {
            return true;
        }
    }

    public function getKey() {
        return $this->_key;
    }

    public function getAction($action) {
        $result = $action[$this->getKey()];
        if ($this->getKey() == 'additional_description' && !empty($action['background_image'])) {
            $block = $this->getLayeredDescriptionBlock();
            $block
                ->setData('additional_description', $action['additional_description'])
                ->setData('background_image', $action['background_image']);
            $result = $block->toHtml();
        }
        return $result;
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

    /**
     * @return ManaPro_FilterContent_Helper_Factory
     */
    public function factoryHelper() {
        return Mage::helper('manapro_filtercontent/factory');
    }

    /**
     * @return ManaPro_FilterContent_Block_LayeredDescription
     */
    public function getLayeredDescriptionBlock() {
        return Mage::getSingleton('core/layout')->getBlockSingleton('manapro_filtercontent/layeredDescription');
    }

    #endregion
}