<?php
/**
 * @category    Mana
 * @package     ManaPro_Slider
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 * @method ManaPro_Slider_Model_Cmsblock getBlock()
 * @method ManaPro_Slider_Block_Cmsblock setBlock(ManaPro_Slider_Model_Cmsblock $value)
 * @method Mage_Core_Model_Config_Element getDisplayOptions()
 * @method ManaPro_Slider_Block_Cmsblock setDisplayOptions(Mage_Core_Model_Config_Element $value)
 */
class ManaPro_Slider_Block_Htmlblock extends ManaPro_Slider_Block_Abstract {
    public function init() {
        $this->setTemplate((string)$this->getDisplayOptions()->template);
        return $this;
    }
    public function getPosition() {
        return $this->getBlock()->getPosition();
    }

    public function getChildName() {
        return $this->getBlock()->getBlockLocalName();
    }

    public function getHtml() {
        /* @var $helper Mage_Cms_Helper_Data */
        $helper = Mage::helper('cms');
        $processor = $helper->getBlockTemplateProcessor();
        $html = $processor->filter($this->getBlock()->getHtml());
        return $html;
    }

    public function isVisible() {
        return true;
    }
}